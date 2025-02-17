<?php
//Leave all this stuff as it is
date_default_timezone_set('America/Sao_Paulo');
include 'GIFEncoder.class.php';
include 'php52-fix.php';
$time = $_GET['time'];
$future_date = new DateTime(date('r',strtotime($time)));
$time_now = time();
$now = new DateTime(date('r', $time_now));
$frames = array();    
$delays = array();

$delay = 100;// milliseconds

$font = array(
    'size' => 40, // Font size, in pts usually.
    'angle' => 0, // Angle of the text
    'x-offset' => 70, // The larger the number the further the distance from the left hand side, 0 to align to the left.
    'y-offset' => 120, // The vertical alignment, trial and error between 20 and 60.
    'file' => __DIR__ . DIRECTORY_SEPARATOR . 'Arial Narrow.otf', // Font path
);

for($i = 0; $i <= 60; $i++){
    
    $interval = date_diff($future_date, $now);
    
    // Open the first source image and add the text.
    $image = imagecreatefrompng('images/bg.png');
    $font['color'] = imagecolorallocate($image, 255,255,255); // Create color after image
    
    if($future_date < $now){
        $text = $interval->format('00:00:00:00');
        imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
        ob_start();
        imagegif($image);
        $frames[]=ob_get_contents();
        $delays[]=$delay;
        $loops = 1;
        ob_end_clean();
        break;
    } else {
        $hour = $interval->format('%H');
        $day = $interval->format('%D');
        $minute = $interval->format('%I');
        $second = $interval->format('%S');
        if ($day > 0) {
            $hour += 1;
        }
        if (strlen($hour) == 1) {
            $hour = '0'.$hour;
            
        } 
        $text = $interval->format( '      '.$day.'   '.$hour.'   '.$minute.'    '.$second);
        imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] -70 , $font['color'] , $font['file'], $text );

        // Add labels
        $labels = "                  Dias          Horas       Minutos     Segundos";
        imagettftext ($image , $font['size'] -25 , $font['angle'] , $font['x-offset'] -10  , $font['y-offset'] -50 , $font['color'] , $font['file'], $labels );

        ob_start();
        imagegif($image);
        $frames[]=ob_get_contents();
        $delays[]=$delay;
        $loops = 0;
        ob_end_clean();
    }

    $now->modify('+1 second');
}

//expire this image instantly
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );
$gif = new AnimatedGif($frames,$delays,$loops);
$gif->display();