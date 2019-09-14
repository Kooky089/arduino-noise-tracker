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
