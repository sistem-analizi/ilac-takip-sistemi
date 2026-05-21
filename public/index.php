<?php
  
  use Illuminate\Foundation\Application;
  use Illuminate\Http\Request;

  define('LARAVEL_START', microtime(true));

  $basePath = null;
  $envFile = __DIR__ . '/../.env';
  if (is_file($envFile)) {
      foreach (file($envFile, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
          $line = trim($line);
          if ($line === '' || $line[0] === '#') continue;
          if (strpos($line, 'APP_BASE_PATH=') === 0) {
              $basePath = trim(substr($line, strlen('APP_BASE_PATH=')));
              $basePath = trim($basePath, "\"'");
              break;
          }
      }
  }
  if ($basePath) {
      $basePath = '/' . trim($basePath, '/');
      $_SERVER['SCRIPT_NAME'] = $basePath . '/index.php';
      $_SERVER['PHP_SELF']    = $basePath . '/index.php';
  }
  // ─────────────────────────────────────────────────────────────────────

  if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
      require $maintenance;
  }
  
  require __DIR__.'/../vendor/autoload.php';

  /** @var Application $app */
  $app = require_once __DIR__.'/../bootstrap/app.php';

  $app->handleRequest(Request::capture());