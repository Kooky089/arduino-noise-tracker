<?php
if (!isset($_GET["state"]) || !isset($_GET["errors"])) {
  echo "fail";
} else {
  $current_time = time();
  $file = "noise_tracker.csv";
  $line = date("Y-m-d");
  $line .= "T";
  $line .= date("H:i:s");
  $line .= ",";
  $line .= $_GET["state"];
  $line .= ",";
  $line .= $_GET["errors"];
  $line .= "\n";
  file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
  echo "success";
}
?>