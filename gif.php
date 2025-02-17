<?php
//Leave all this stuff as it is
date_default_timezone_set('America/Sao_Paulo');
include 'GIFEncoder.class.php';
include 'php52-fix.php';
$future_date = new DateTime(date('r', strtotime($time)));
$now = new DateTime(date('r', time()));

for ($i = 0; $i <= 60; $i++) {
    $interval = $future_date->diff($now);
    
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

        $text = sprintf("      %02d   %02d   %02d    %02d", $day, $hour, $minute, $second);
    }

    $image = imagecreatefrompng('images/bg.png');
    $font['color'] = imagecolorallocate($image, 255, 255, 255);
    
    imagettftext($image, $font['size'], $font['angle'], $font['x-offset'], $font['y-offset'] - 70, $font['color'], $font['file'], $text);
    
    ob_start();
    imagegif($image);
    $frames[] = ob_get_contents();
    $delays[] = 100;
    ob_end_clean();
    
    imagedestroy($image);

    $now->modify('+1 second');
}

header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

$gif = new AnimatedGif($frames, $delays, 0);
$gif->display();
