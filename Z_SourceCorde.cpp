#include <WiFi.h>
#include <WiFiClientSecure.h> // Wajib ditambahkan untuk koneksi TLS/SSL
#include <PubSubClient.h>

// ================= PENGATURAN WIFI & HIVEMQ CLOUD =================
const char* ssid = "Securely";
const char* password = "yor12345y";

// URL dan Port disesuaikan dengan gambar HiveMQ Cloud
const char* mqtt_server = "526e2ab6e3ef4537b83d263fc7f28b01.s1.eu.hivemq.cloud";
const int mqtt_port = 8883; // Port untuk TLS MQTT

// PENTING: Pastikan user & pass ini sudah kamu buat di tab "Access Management" di dashboard HiveMQ
const char* mqtt_user = "Yorsan"; 
const char* mqtt_pass = "Yorsan123y"; 

// ================= PENGATURAN PIN (ESP32 38 PIN) =================
const int pinMQ2 = 33;
const int pinFlame = 35;
const int relayFan = 26;
const int relayPump = 27;
const int pinBuzzer = 25; 

// ================= THRESHOLD =================
int thresholdGas = 3000;
int thresholdFire = 2000;

// ================= TRACKING STATUS =================
bool isFanOn = false;
bool isPumpOn = false;
bool isBuzzerOn = false; 

WiFiClientSecure espClient; // Menggunakan Secure Client
PubSubClient client(espClient);

void setup_wifi() {
  delay(10);
  Serial.println("\nConnecting to WiFi...");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected!");
}

void callback(char* topic, byte* payload, unsigned int length) {
  String message = "";
  for (int i = 0; i < length; i++) message += (char)payload[i];
  
  // Kontrol manual dari Web/Railway
  if (message == "FORCE_FAN_ON") {
    digitalWrite(relayFan, HIGH);
    isFanOn = true;
  }
  if (message == "FORCE_FAN_OFF") {
    digitalWrite(relayFan, LOW);
    isFanOn = false;
  }
}

void reconnect() {
  while (!client.connected()) {
    Serial.print("Attempting MQTT connection...");
    // Menggunakan parameter Client ID, Username, dan Password
    if (client.connect("ESP32_Fire_Unit", mqtt_user, mqtt_pass)) {
      Serial.println("connected to HiveMQ!");
      client.subscribe("chaos/command");
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println(" try again in 5 seconds");
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  pinMode(pinMQ2, INPUT);
  pinMode(pinFlame, INPUT);
  pinMode(relayFan, OUTPUT);
  pinMode(relayPump, OUTPUT);
  pinMode(pinBuzzer, OUTPUT); 
  
  // Pastikan semua aktuator mati saat pertama kali menyala
  digitalWrite(relayFan, LOW);
  digitalWrite(relayPump, LOW);
  digitalWrite(pinBuzzer, LOW); 

  setup_wifi();
  
  // Bypass verifikasi sertifikat SSL untuk mempermudah koneksi (standar ESP32 ke public broker)
  espClient.setInsecure(); 
  
  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);

  Serial.println("Sistem Online & Terhubung ke WiFi!");
}

void loop() {
  if (!client.connected()) reconnect();
  client.loop();

  int nilaiGas = analogRead(pinMQ2);
  int nilaiApi = analogRead(pinFlame);

  // ================= 1. TELEMETRI KE HIVEMQ (Tiap 2 Detik) =================
  static unsigned long lastMsg = 0;
  if (millis() - lastMsg > 2000) {
    lastMsg = millis();
    String payload = "{\"gas\":" + String(nilaiGas) + ",\"api\":" + String(nilaiApi) + "}";
    client.publish("chaos/sensor/data", payload.c_str());
  }

  // ================= 2. BACA STATUS SENSOR (Anti-Spam) =================
  static bool statusGasBahaya = false;
  static bool statusApiBahaya = false;

  // Gas: Makin tinggi nilai analog = gas makin pekat
  if (nilaiGas > thresholdGas) statusGasBahaya = true;
  else if (nilaiGas < (thresholdGas - 300)) statusGasBahaya = false;

  // Api: Makin rendah nilai analog = api makin dekat/besar
  if (nilaiApi < thresholdFire) statusApiBahaya = true;
  else if (nilaiApi > (thresholdFire + 1000)) statusApiBahaya = false;


  // ================= 3. LOGIKA KONDISI UTAMA =================
  bool targetFan = false;
  bool targetPump = false;
  bool targetBuzzer = false; 

  if (statusGasBahaya && !statusApiBahaya) {
    // Kondisi 1: Cuma ada Gas (Bocor)
    targetFan = true;   
    targetPump = false; 
    targetBuzzer = true;  // Kipas & Buzzer Nyala
  } 
  else if (!statusGasBahaya && statusApiBahaya) {
    // Kondisi 2: Cuma ada Api
    targetFan = true;   
    targetPump = true;  
    targetBuzzer = true;  // Kipas, Pompa, & Buzzer Nyala
  } 
  else if (statusGasBahaya && statusApiBahaya) {
    // Kondisi 3: Paling Gawat (Gas bocor + Ada Api)
    targetFan = true;   
    targetPump = true;  
    targetBuzzer = true;  // Semua komponen aktif
  } 
  else {
    // Kondisi 4: Aman Terkendali
    targetFan = false;  
    targetPump = false; 
    targetBuzzer = false; // Semua komponen nonaktif
  }

  // ================= 4. EKSEKUSI AKTUATOR & REPORT MQTT =================
  
  // Kontrol Kipas
  if (isFanOn != targetFan) { 
    isFanOn = targetFan;
    digitalWrite(relayFan, isFanOn ? HIGH : LOW);
    
    if (isFanOn) client.publish("chaos/report", "ACTION: Kipas ON");
    else client.publish("chaos/report", "INFO: Kipas OFF");
  }

  // Kontrol Pompa
  if (isPumpOn != targetPump) { 
    isPumpOn = targetPump;
    digitalWrite(relayPump, isPumpOn ? HIGH : LOW);
    
    if (isPumpOn) client.publish("chaos/report", "CRITICAL: API TERDETEKSI! Pompa ON");
    else client.publish("chaos/report", "INFO: Api Padam. Pompa OFF");
  }

  // Kontrol Buzzer 
  if (isBuzzerOn != targetBuzzer) {
    isBuzzerOn = targetBuzzer;
    digitalWrite(pinBuzzer, isBuzzerOn ? HIGH : LOW);
    
    if (isBuzzerOn) client.publish("chaos/report", "WARNING: Bahaya terdeteksi! Buzzer ON");
    else client.publish("chaos/report", "INFO: Situasi normal. Buzzer OFF");
  }

  delay(100);
}