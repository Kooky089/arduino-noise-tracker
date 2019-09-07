// Noise Tracker
// Can track the operating state of devices through their noise and pushes data to given URL

#include <ESP8266WiFi.h>

//replace this with your WiFi network name
const char* ssid = "";
//replace this with your WiFi network password
const char* password = "";
//server data
struct destination_t {
  const char* host = "example.de";
  const int port = 80;
  const char* path = "/noise_tracker.php";
};

// duration of a frame in msec
const int frame_duration = 2000;
// frame is considered active if its average value is above threshold
const double threshold = 0.2;
// change state if given number of consecutive frames yield the same state
const int frames = 10;

// pin of noise sensor
const int input_pin = 16;

// makes sure WiFi is connected or returns false
bool wifi_connect() {
  if (WiFi.status() == WL_CONNECTED) {
    return true;
  }
  Serial.printf("Connecting WiFi");
  WiFi.begin(ssid, password);
  int timeout = 0;
  for (int timeout = 0; timeout < 10; timeout++) {
    Serial.printf(".");
    delay(1000);
    if (WiFi.status() == WL_CONNECTED) {
      Serial.printf("\n");
      return true;
    }
  }
  Serial.print("\n");
  return false;
}

void wifi_disconnect() {
  Serial.printf("Disconnecting WiFi\n");
  WiFi.disconnect();
}

void set_led(bool state) {
  digitalWrite(LED_BUILTIN, state?LOW:HIGH);
}

// push state and number of errors to destination
bool upload(const destination_t destination, bool state, int errors) {
  if (!wifi_connect()) {
    return false;
  }
  WiFiClient client;
  if (client.connect(destination.host, destination.port)) {
    client.printf("GET %s?state=%d&errors=%d HTTP/1.0\n", destination.path, state?1:0, errors);
    client.printf("Host: %s\n\n", destination.host);
    while (client.connected()) {
      // Server closes connection after reply has been fully sent. Wait until then.
      delay(10);
    }
    String reply;
    while (client.available()) {
      reply += (char) client.read();
    }
    if (reply.indexOf("success") >= 0) {
      Serial.printf("Server replied success\n");
      if (!state) {
        // keep WiFi alive if state turned on (will propably turn false soon)
        wifi_disconnect();
      }
      return true;
    } else if (reply.indexOf("fail") >= 0) {
      Serial.printf("Server replied failure\n");
    } else {
      Serial.printf("Server replied something else\n");
    }
    Serial.println(reply);
  }
  wifi_disconnect();
  return false;
}

// commit new state if it has changed
void set_state(bool state) {
  static bool _state = true;
  static int errors = 0;
  if (state != _state) {
    printf("Changed state to %d\n", state);
    set_led(state);
    if (upload(destination_t(), state, errors)) {
      errors = 0;
    } else {
      errors++;
    }
    _state = state;
  }
}

// measure one frame and return state
bool check_frame() {
  int high_count = 0;
  int low_count = 0;
  for (int i = 0; i < frame_duration; i++) {
    if (digitalRead(input_pin) == LOW) {
      ++low_count;
    } else {
      ++high_count;
    }
    delay(1);
  }
  return 1.0 * low_count / frame_duration > threshold;
}

// return state if given number (#frames) of consecutive frames yield the same state
bool check_state() {
  bool last_state = check_frame();
  int count = 1;
  while (count < frames) {
    if (last_state == check_frame()) {
      count++;
    } else {
      last_state = !last_state;
      count = 1;
    }
  }
  return last_state;
}

void setup() {
  Serial.begin(115200);
  pinMode(input_pin, INPUT);
  pinMode(LED_BUILTIN, OUTPUT);
  set_state(false);
}

void loop() {
  set_state(check_state());
}
