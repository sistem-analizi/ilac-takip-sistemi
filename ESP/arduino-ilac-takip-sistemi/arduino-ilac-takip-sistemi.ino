#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>

// ==========================================
// AĞ VE SUNUCU AYARLARI
// ==========================================
const char* ssid = "WIFI_ADINIZ";         
const char* password = "WIFI_SIFRENIZ"; 

// Yerelde test etmek için: true | Canlıda test etmek için: false
const bool yerelSunucudaCalis = false; 

const String yerelURL = "http://192.168.1.X:8000"; 
const String canliURL = "https://proje.kamilakgun.com.tr/2026/ilac-takip-sistemi";
String apiRoot = ""; 
// ==========================================

const int bolmeSayisi = 8; 
const int bolmeIDleri[] = {1, 2, 3, 4, 5, 6, 7, 8};
const int ledKirmiziPinleri[] = {27, 26, 22, 19, 12, 23, 5, 2};
const int ledYesilPinleri[]   = {32, 25, 21, 18, 13, 33, 4, 15};
const int butonPin = 14;
const int buzzerPin = 0;

bool butonBasildiMi = false;
int bolmeDurumlari[] = {0, 0, 0, 0, 0, 0, 0, 0};
unsigned long sonSorguZamani = 0;
const long sorguAraligi = 3000; 

int scrollPos = 128; 
unsigned long sonAnimasyonZamani = 0;
unsigned long sonBuzzerZamani = 0;
const int animasyonHizi = 20; 
String kayanMesaj = "";

#define i2c_SDA 16
#define i2c_SCL 17
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_ADDRESS 0x3c 
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);

WiFiClientSecure client;

void tekSatirKayanGoster(String mesaj) {
  display.clearDisplay();
  display.setTextSize(2);
  display.setTextColor(SSD1306_WHITE);
  display.setTextWrap(false);
  display.setCursor(scrollPos, 24);
  display.print(mesaj);
  display.display();

  if (millis() - sonAnimasyonZamani >= animasyonHizi) {
    scrollPos -= 3; 
    if (scrollPos < -(int)(mesaj.length() * 14)) {
      scrollPos = 128;
    }
    sonAnimasyonZamani = millis();
  }

  if (millis() - sonBuzzerZamani >= 1000) {
    digitalWrite(buzzerPin, HIGH);
    delay(50);
    digitalWrite(buzzerPin, LOW);
    sonBuzzerZamani = millis();
  }
}

void beklemeEkrani() {
  display.clearDisplay();
  display.setTextSize(1);
  display.setCursor(30, 28);
  display.print("SISTEM HAZIR");
  display.display();
  digitalWrite(buzzerPin, LOW);
}

void setup() {
  Serial.begin(115200);

  if (yerelSunucudaCalis) {
    apiRoot = yerelURL;
    Serial.println("Mod: Yerel Sunucu (Localhost) aktif.");
  } else {
    apiRoot = canliURL;
    Serial.println("Mod: Canli Sunucu aktif.");
  }
  Serial.print("Hedef API Adresi: ");
  Serial.println(apiRoot);

  pinMode(butonPin, INPUT_PULLUP);
  pinMode(buzzerPin, OUTPUT);
  digitalWrite(buzzerPin, LOW);

  client.setInsecure();

  Wire.begin(i2c_SDA, i2c_SCL);
  if(!display.begin(SSD1306_SWITCHCAPVCC, OLED_ADDRESS)) {
    for(;;);
  }
  
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(10, 20);
  display.println("WiFi Baglaniyor...");
  display.display();

  for (int i = 0; i < bolmeSayisi; i++) {
    pinMode(ledKirmiziPinleri[i], OUTPUT);
    pinMode(ledYesilPinleri[i], OUTPUT);
    digitalWrite(ledKirmiziPinleri[i], LOW);
    digitalWrite(ledYesilPinleri[i], HIGH);
  }

  WiFi.begin(ssid, password);
  int counter = 0;
  while (WiFi.status() != WL_CONNECTED) { 
    delay(500); 
    Serial.print(".");
    display.print(".");
    display.display();
    if(counter++ > 20) break; 
  }

  beklemeEkrani();
}

void loop() {
  if (digitalRead(butonPin) == LOW && !butonBasildiMi) {
    digitalWrite(buzzerPin, LOW); 
    if (WiFi.status() == WL_CONNECTED) {
      for (int i = 0; i < bolmeSayisi; i++) {
        if (bolmeDurumlari[i] == 1) { 
          HTTPClient http;
          String url = apiRoot + "/api/buton-basildi/" + String(bolmeIDleri[i]);
          http.begin(client, url);
          
          int httpCode = http.POST("");
          if (httpCode > 0) {
            bolmeDurumlari[i] = 0;
            digitalWrite(ledKirmiziPinleri[i], LOW);
            digitalWrite(ledYesilPinleri[i], HIGH);
          }
          http.end();
        }
      }
    }
    kayanMesaj = ""; 
    butonBasildiMi = true;
  }
  if (digitalRead(butonPin) == HIGH) { butonBasildiMi = false; }

  if (millis() - sonSorguZamani >= sorguAraligi) {
    sonSorguZamani = millis();
    if (WiFi.status() == WL_CONNECTED) {
      HTTPClient http;
      String url = apiRoot + "/api/led-durum-hepsi";
      http.begin(client, url);
      
      int httpCode = http.GET();
      if (httpCode == 200) {
        JsonDocument doc;
        deserializeJson(doc, http.getString());
        
        String aktifBolmeler = "";
        bool durumVar = false;

        for (int i = 0; i < bolmeSayisi; i++) {
          int durum = doc["bolme_" + String(bolmeIDleri[i])] | 0;
          bolmeDurumlari[i] = durum;
          if (durum == 1) {
            digitalWrite(ledKirmiziPinleri[i], HIGH);
            digitalWrite(ledYesilPinleri[i], LOW);
            aktifBolmeler += "B" + String(bolmeIDleri[i]) + " ";
            durumVar = true;
          } else {
            digitalWrite(ledKirmiziPinleri[i], LOW);
            digitalWrite(ledYesilPinleri[i], HIGH);
          }
        }
        
        if (durumVar) {
          kayanMesaj = aktifBolmeler + "ILAC VAKTI!          ";
        } else {
          kayanMesaj = "";
        }
      } else {
        Serial.print("HTTP Hata Kodu: ");
        Serial.println(httpCode);
      }
      http.end();
    }
  }

  if (kayanMesaj != "") {
    tekSatirKayanGoster(kayanMesaj);
  } else {
    beklemeEkrani();
  }
}