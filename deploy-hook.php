<?php
/**
 * Deploy hook — CI sonrası otomatik tetiklenir.
 *
 * Görevler:
 *   1) vendor.zip dosyası varsa: extract et → vendor.zip'i sil
 *   2) Laravel artisan komutlarını çalıştır: migrate, config:cache, vb.
 *
 * Mimari:
 *   - ZipArchive ile extract (shell_exec'siz)
 *   - Laravel Kernel::call() ile artisan (shell_exec'siz)
 *
 * Güvenlik:
 *   - Hook key .env'deki DEPLOY_HOOK_KEY'den okunur
 *   - URL: https://.../deploy-hook.php?key=<DEPLOY_HOOK_KEY>
 *   - hash_equals() ile timing-safe karşılaştırma
 */

declare(strict_types=1);

// ────────── 1) Güvenlik kontrolü ──────────
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    http_response_code(500);
    die('.env not found');
}

// Laravel .env formatı PHP'nin parse_ini_file()'ı için tam uyumlu değil
// (${VAR} interpolation, mixed quotes). Manuel parse ediyoruz.
$hookKey = read_env_value($envPath, 'DEPLOY_HOOK_KEY');

if (empty($hookKey)) {
    http_response_code(500);
    die('DEPLOY_HOOK_KEY not configured in .env');
}

$provided = $_GET['key'] ?? '';
if (!hash_equals($hookKey, (string)$provided)) {
    http_response_code(403);
    die('Forbidden');
}

// ────────── 2) Log header ──────────
header('Content-Type: text/plain; charset=utf-8');
@set_time_limit(300);  // 5 dakika
@ini_set('memory_limit', '512M');

echo "═══════════════════════════════════════════════════════\n";
echo "  Deploy hook starting — " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════\n\n";

// ────────── basePath fix ──────────
// public/index.php aynı düzeltmeyi yapıyor. Hook ayrı bir entrypoint olduğu
// için burada da uygulamamız lazım — yoksa Console Kernel bootstrap sırasında
// (PackageManifest::build vb.) Laravel URL/base path'i yanlış initialize edip
// buggy cache yazıyor → ana proje 405 dönüyor.
$basePath = read_env_value($envPath, 'APP_BASE_PATH');
if ($basePath) {
    $basePath = '/' . trim($basePath, '/');
    $_SERVER['SCRIPT_NAME'] = $basePath . '/index.php';
    $_SERVER['PHP_SELF']    = $basePath . '/index.php';
    $_SERVER['REQUEST_URI'] = $basePath . '/';
}

chdir(__DIR__);

// ────────── 3) vendor.zip extract (varsa) ──────────
$zipPath = __DIR__ . '/vendor.zip';

if (file_exists($zipPath)) {
    echo "→ vendor.zip found (" . formatBytes(filesize($zipPath)) . ")\n";

    if (!class_exists('ZipArchive')) {
        echo "::: ERROR: ZipArchive PHP extension not available\n";
        http_response_code(500);
        exit;
    }

    // Eski vendor/ sil (composer.lock değiştiyse stale dosyalar kalmasın)
    if (is_dir(__DIR__ . '/vendor')) {
        echo "  - Removing old vendor/...\n";
        $t0 = microtime(true);
        rrmdir(__DIR__ . '/vendor');
        echo "  ✓ removed in " . round(microtime(true) - $t0, 2) . "s\n";
    }

    // Extract
    echo "  - Extracting vendor.zip...\n";
    $t0 = microtime(true);

    $zip = new ZipArchive();
    $opened = $zip->open($zipPath);
    if ($opened !== true) {
        echo "::: ERROR: failed to open vendor.zip (code $opened)\n";
        http_response_code(500);
        exit;
    }

    if (!$zip->extractTo(__DIR__)) {
        echo "::: ERROR: extractTo() failed\n";
        $zip->close();
        http_response_code(500);
        exit;
    }

    $zip->close();
    unlink($zipPath);

    echo "  ✓ extracted in " . round(microtime(true) - $t0, 2) . "s\n\n";
} else {
    echo "→ no vendor.zip — skipping extract\n\n";
}

// ────────── 4) Vendor doğrulama ──────────
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "::: ERROR: vendor/autoload.php not found\n";
    http_response_code(500);
    exit;
}

// ────────── 5) Laravel'i bootstrap'le ──────────
echo "→ Bootstrapping Laravel...\n";

try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    /** @var \Illuminate\Contracts\Console\Kernel $kernel */
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    echo "  ✓ Laravel " . $app->version() . "\n\n";
} catch (\Throwable $e) {
    echo "::: ERROR: bootstrap failed — " . $e->getMessage() . "\n";
    http_response_code(500);
    exit;
}

// ────────── 6) Storage symlink — exec() yerine native PHP symlink() ──────
// Plesk vb. shared hosting'de exec() çoğu zaman disable_functions'da.
// Laravel'in storage:link komutu bazı codepath'lerde exec'e gidiyor.
// PHP'nin native symlink()'ini doğrudan çağırarak bunu bypass ediyoruz.
$storageTarget = __DIR__ . '/storage/app/public';
$storageLink   = __DIR__ . '/public/storage';

echo "→ storage symlink (native PHP)\n";
if (!is_dir($storageTarget)) {
    @mkdir($storageTarget, 0775, true);
    echo "    created target dir: $storageTarget\n";
}
if (is_link($storageLink) || file_exists($storageLink)) {
    echo "    ✓ already exists, skipping\n";
} else {
    if (!function_exists('symlink')) {
        echo "    ⚠ symlink() function disabled. Storage uploads won't work via public/storage path.\n";
    } elseif (@symlink($storageTarget, $storageLink)) {
        echo "    ✓ linked $storageLink → $storageTarget\n";
    } else {
        $err = error_get_last();
        echo "    ⚠ symlink failed: " . ($err['message'] ?? 'unknown') . "\n";
        echo "      (Hosting might disallow symlinks. Yedek olarak public/storage'i\n";
        echo "       gerçek bir klasör yapıp Laravel'in storage path'ini değiştir.)\n";
    }
}
echo "\n";

// ────────── 7) Artisan komutları ──────────
//
// SADECE migrate. *:clear komutları KALDIRILDI — Kernel::call() bootstrap'i
// HTTP context'te buggy cache yazıyordu. Cache temizliği aşağıda (adım 8)
// fiziksel unlink ile yapılıyor (artisan'a hiç gitmeden).
$commands = [
    'migrate' => ['--force' => true],
];

$allOk = true;
foreach ($commands as $cmd => $opts) {
    echo "→ artisan $cmd\n";
    try {
        $exitCode = $kernel->call($cmd, $opts);
        $output   = trim((string)$kernel->output());
        if ($output !== '') {
            echo "    " . str_replace("\n", "\n    ", $output) . "\n";
        }
        if ($exitCode !== 0) {
            echo "  ⚠ exit $exitCode\n";
        }
    } catch (\Throwable $e) {
        echo "  ✗ " . $e->getMessage() . "\n";
        $allOk = false;
    }
    echo "\n";
}

// ────────── 8) bootstrap/cache fiziksel temizlik ──────────
// Hook'un Console Kernel bootstrap'i (migrate vb. çalışırken) sırasında
// PackageManifest / config / route cache dosyalarını HTTP context'te
// yazıyor olabilir. Hook bittikten SONRA bunları fiziksel olarak silelim
// ki app bir sonraki HTTP request'te cache'siz (runtime'da) yüklensin.
// packages.php ve services.php tutuluyor — bunlar package:discover'ın
// ürünü, kritik değil.
echo "→ bootstrap/cache physical wipe\n";
$wiped = 0;
foreach (glob(__DIR__ . '/bootstrap/cache/*.php') ?: [] as $f) {
    $base = basename($f);
    if ($base === 'packages.php' || $base === 'services.php') continue;
    if (@unlink($f)) {
        echo "    ✓ removed $base\n";
        $wiped++;
    }
}
echo "  $wiped file(s) removed\n\n";

echo "═══════════════════════════════════════════════════════\n";
echo "  Deploy hook completed — " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════\n";

if (!$allOk) {
    http_response_code(500);
}

// ────────── Helpers ──────────

/**
 * Laravel .env formatından tek bir değer oku.
 * parse_ini_file()'a benzer ama daha esnek: ${VAR} interpolation'ları,
 * tek/çift tırnak karışımı, yorum satırları gibi durumları handle eder.
 */
function read_env_value(string $path, string $key): ?string
{
    if (!is_file($path)) {
        return null;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $eqPos = strpos($line, '=');
        if ($eqPos === false) {
            continue;
        }
        $k = trim(substr($line, 0, $eqPos));
        if ($k !== $key) {
            continue;
        }
        $v = trim(substr($line, $eqPos + 1));
        // Sondaki inline yorumu kes (sadece tırnak dışındaysa)
        if ($v !== '' && $v[0] !== '"' && $v[0] !== "'") {
            $hashPos = strpos($v, ' #');
            if ($hashPos !== false) {
                $v = trim(substr($v, 0, $hashPos));
            }
        }
        // Çevreleyen tırnakları sıyır
        $len = strlen($v);
        if ($len >= 2 && (
            ($v[0] === '"' && $v[$len - 1] === '"') ||
            ($v[0] === "'" && $v[$len - 1] === "'")
        )) {
            $v = substr($v, 1, -1);
        }
        return $v;
    }
    return null;
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = array_diff(scandir($dir) ?: [], ['.', '..']);
    foreach ($items as $item) {
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path) && !is_link($path)) {
            rrmdir($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

function formatBytes(int $bytes): string
{
    if ($bytes < 1024) return "$bytes B";
    if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . " KB";
    if ($bytes < 1024 * 1024 * 1024) return round($bytes / (1024 * 1024), 1) . " MB";
    return round($bytes / (1024 * 1024 * 1024), 2) . " GB";
}
