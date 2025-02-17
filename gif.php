<?php
// Set timezone
date_default_timezone_set('America/Sao_Paulo');

// Include required files
include 'GIFEncoder.class.php';
include 'php52-fix.php';

// Get time from URL parameters
$time = $_GET['time'] ?? 'now';

// Convert input time to DateTime object
$future_date = new DateTime(date('r', strtotime($time)));
$now = new DateTime(date('r', time()));

// Prepare arrays for frames and delays
$frames = array();
$delays = array();

$delay = 100; // milliseconds

// Font settings
$font = array(
    'size' => 40, // Font size
    'angle' => 0, // Angle of the text
    'x-offset' => 70, // Horizontal alignment
    'y-offset' => 120, // Vertical alignment
    'file' => __DIR__ . DIRECTORY_SEPARATOR . 'Arial Narrow.otf', // Font path
);

// Ensure font file exists
if (!file_exists($font['file'])) {
    die('Error: Font file not found!');
}

// Generate GIF frames
for ($i = 0; $i <= 60; $i++) {
    $interval = $future_date->diff($now);

    // Create image from background
    $image = imagecreatefrompng('images/bg.png');
    $font['color'] = imagecolorallocate($image, 255, 255, 255); // White text color

    // If time has already passed
    if ($future_date->getTimestamp() < $now->getTimestamp()) {
        $text = '00:00:00:00';
    } else {
        $day = $interval->days;
        $hour = $interval->h;
        $minute = $interval->i;
        $second = $interval->s;

        if ($day > 0) {
            $hour += 1;
        }

        // Ensure two-digit formatting
        $text = sprintf("      %02d   %02d   %02d    %02d", $day, $hour, $minute, $second);
    }

    // Add countdown text to image
    imagettftext($image, $font['size'], $font['angle'], $font['x-offset'], $font['y-offset'] - 70, $font['color'], $font['file'], $text);

    // Add labels (Days, Hours, Minutes, Seconds)
    $labels = "                  Dias          Horas       Minutos     Segundos";
    imagettftext($image, $font['size'] - 25, $font['angle'], $font['x-offset'] - 10, $font['y-offset'] - 50, $font['color'], $font['file'], $labels);

    // Capture image data as GIF
    ob_start();
    imagegif($image);
    $frames[] = ob_get_contents();
    $delays[] = $delay;
    ob_end_clean();

    // Free memory
    imagedestroy($image);

    // Increment time
    $now->modify('+1 second');
}

// Set HTTP headers (avoid caching)
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Generate and output animated GIF
$gif = new AnimatedGif($frames, $delays, 0);
$gif->display();
