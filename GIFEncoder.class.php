<?php
 
/*
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: Formerly known as:::
:: GIFEncoder Version 2.0 by László Zsidi, http://gifs.hu
::
:: This class is a rewritten 'GifMerge.class.php' version.
::
:: Modification:
:: - Simplified and easy code,
:: - Ultra fast encoding,
:: - Built-in errors,
:: - Stable working
::
::
:: Updated at 2007. 02. 13. '00.05.AM'
::
::
::
:: Try on-line GIFBuilder Form demo based on GIFEncoder.
::
:: http://gifs.hu/phpclasses/demos/GifBuilder/
::
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
*/
 
/**
* Encode animated gifs
*/
class AnimatedGif {
 
/**
* The built gif image
* @var resource
*/
private $image = '';
 
/**
* The array of images to stack
* @var array
*/
private $buffer = Array();
 
/**
* How many times to loop? 0 = infinite
* @var int
*/
private $number_of_loops = 0;
 
/**
*
* @var int
*/
private $DIS = 2;
 
/**
* Which colour is transparent
* @var int
*/
private $transparent_colour = -1;
 
/**
* Is this the first frame
* @var int
*/
private $first_frame = TRUE;
 
/**
* Encode an animated gif
* @param array $source_images An array of binary source images
* @param array $image_delays The delays associated with the source images
* @param type $number_of_loops The number of times to loop
* @param int $transparent_colour_red
* @param int $transparent_colour_green
* @param int $transparent_colour_blue
*/
function __construct(array $source_images, array $image_delays, $number_of_loops, $transparent_colour_red = -1, $transparent_colour_green = -1, $transparent_colour_blue = -1) {
/**
* I have no idea what these even do, they appear to do nothing to the image so far
*/
$transparent_colour_red = 0;
$transparent_colour_green = 0;
$transparent_colour_blue = 0;
 
$this->number_of_loops = ( $number_of_loops > -1 ) ? $number_of_loops : 0;
$this->set_transparent_colour($transparent_colour_red, $transparent_colour_green, $transparent_colour_blue);
$this->buffer_images($source_images);
 
$this->addHeader();
for ($i = 0; $i < count($this->buffer); $i++) {
$this->addFrame($i, $image_delays [$i]);
}
}
/**
* Set the transparent colour
* @param int $red
* @param int $green
* @param int $blue
*/
private function set_transparent_colour($red, $green, $blue){
$this->transparent_colour = ( $red > -1 && $green > -1 && $blue > -1 ) ?
( $red | ( $green << 8 ) | ( $blue << 16 ) ) : -1;
}
 
/**
 * Buffer the images and check to make sure they are valid
 * @param array $source_images the array of source images
 * @throws Exception
 */
private function buffer_images($source_images) {
    if (!is_array($source_images)) {
        throw new Exception('Invalid input: expected an array of image data.');
    }

    $this->buffer = []; // Ensure buffer is initialized

    for ($i = 0; $i < count($source_images); $i++) {
        $this->buffer[] = $source_images[$i];

        // Ensure we have enough data before checking
        if (strlen($this->buffer[$i]) < 6 || 
            (substr($this->buffer[$i], 0, 6) != "GIF87a" && substr($this->buffer[$i], 0, 6) != "GIF89a")) {
            throw new Exception('Image at position ' . $i . ' is not a valid GIF.');
        }

        // Calculate offset for color table
        $offset = 13 + 3 * (2 << (ord($this->buffer[$i][10]) & 0x07));

        for ($j = $offset, $k = true; $k; $j++) {
            if (!isset($this->buffer[$i][$j])) {
                break; // Prevent out-of-bounds access
            }

            switch ($this->buffer[$i][$j]) {
                case "!":
                    if (isset($this->buffer[$i][$j + 3]) && substr($this->buffer[$i], $j + 3, 8) === "NETSCAPE") {
                        throw new Exception('You cannot make an animation from an animated GIF.');
                    }
                    break;
                case ";":
                    $k = false;
                    break;
            }
        }
    }
}
 
/**
 * Add the GIF header to the image
 */
private function addHeader() {
    $cmap = 0;
    $this->image = 'GIF89a';

    // Ensure buffer exists and contains enough data
    if (!isset($this->buffer[0]) || strlen($this->buffer[0]) < 13) {
        throw new Exception("Buffer is empty or invalid.");
    }

    // Check if the GIF has a global color table (bit 0x80 is set)
    if (ord($this->buffer[0][10]) & 0x80) {
        $cmap = 3 * (2 << (ord($this->buffer[0][10]) & 0x07));

        // Append header and color table
        $this->image .= substr($this->buffer[0], 6, 7);
        $this->image .= substr($this->buffer[0], 13, $cmap);

        // Add Netscape loop extension
        $this->image .= "!\xFF\x0BNETSCAPE2.0\x03\x01" . $this->word($this->number_of_loops) . "\x00";
    }
}
 
/**
* Add a frame to the animation
* @param int $frame The frame to be added
* @param int $delay The delay associated with the frame
*/
private function addFrame($frame, $delay) {
$Locals_str = 13 + 3 * ( 2 << ( ord($this->buffer [$frame] { 10 }) & 0x07 ) );
 
$Locals_end = strlen($this->buffer [$frame]) - $Locals_str - 1;
$Locals_tmp = substr($this->buffer [$frame], $Locals_str, $Locals_end);
 
$Global_len = 2 << ( ord($this->buffer [0] { 10 }) & 0x07 );
$Locals_len = 2 << ( ord($this->buffer [$frame] { 10 }) & 0x07 );
 
$Global_rgb = substr($this->buffer [0], 13, 3 * ( 2 << ( ord($this->buffer [0] { 10 }) & 0x07 ) ));
$Locals_rgb = substr($this->buffer [$frame], 13, 3 * ( 2 << ( ord($this->buffer [$frame] { 10 }) & 0x07 ) ));
 
$Locals_ext = "!\xF9\x04" . chr(( $this->DIS << 2 ) + 0) .
chr(( $delay >> 0 ) & 0xFF) . chr(( $delay >> 8 ) & 0xFF) . "\x0\x0";
 
if ($this->transparent_colour > -1 && ord($this->buffer [$frame] { 10 }) & 0x80) {
for ($j = 0; $j < ( 2 << ( ord($this->buffer [$frame] { 10 }) & 0x07 ) ); $j++) {
if (
ord($Locals_rgb { 3 * $j + 0 }) == ( ( $this->transparent_colour >> 16 ) & 0xFF ) &&
ord($Locals_rgb { 3 * $j + 1 }) == ( ( $this->transparent_colour >> 8 ) & 0xFF ) &&
ord($Locals_rgb { 3 * $j + 2 }) == ( ( $this->transparent_colour >> 0 ) & 0xFF )
) {
$Locals_ext = "!\xF9\x04" . chr(( $this->DIS << 2 ) + 1) .
chr(( $delay >> 0 ) & 0xFF) . chr(( $delay >> 8 ) & 0xFF) . chr($j) . "\x0";
break;
}
}
}
switch ($Locals_tmp { 0 }) {
case "!":
$Locals_img = substr($Locals_tmp, 8, 10);
$Locals_tmp = substr($Locals_tmp, 18, strlen($Locals_tmp) - 18);
break;
case ",":
$Locals_img = substr($Locals_tmp, 0, 10);
$Locals_tmp = substr($Locals_tmp, 10, strlen($Locals_tmp) - 10);
break;
}
if (ord($this->buffer [$frame] { 10 }) & 0x80 && $this->first_frame === FALSE) {
if ($Global_len == $Locals_len) {
if ($this->blockCompare($Global_rgb, $Locals_rgb, $Global_len)) {
$this->image .= ( $Locals_ext . $Locals_img . $Locals_tmp );
} else {
$byte = ord($Locals_img { 9 });
$byte |= 0x80;
$byte &= 0xF8;
$byte |= ( ord($this->buffer [0] { 10 }) & 0x07 );
$Locals_img { 9 } = chr($byte);
$this->image .= ( $Locals_ext . $Locals_img . $Locals_rgb . $Locals_tmp );
}
} else {
$byte = ord($Locals_img { 9 });
$byte |= 0x80;
$byte &= 0xF8;
$byte |= ( ord($this->buffer [$frame] { 10 }) & 0x07 );
$Locals_img { 9 } = chr($byte);
$this->image .= ( $Locals_ext . $Locals_img . $Locals_rgb . $Locals_tmp );
}
} else {
$this->image .= ( $Locals_ext . $Locals_img . $Locals_tmp );
}
$this->first_frame = FALSE;
}
 
/**
* Add the gif footer
*/
private function addFooter() {
$this->image .= ";";
}
 
/**
* Compare gif blocks? What is a block?
* @param type $GlobalBlock
* @param type $LocalBlock
* @param type $Len
* @return type
*/
private function blockCompare($GlobalBlock, $LocalBlock, $Len) {
for ($i = 0; $i < $Len; $i++) {
if (
$GlobalBlock { 3 * $i + 0 } != $LocalBlock { 3 * $i + 0 } ||
$GlobalBlock { 3 * $i + 1 } != $LocalBlock { 3 * $i + 1 } ||
$GlobalBlock { 3 * $i + 2 } != $LocalBlock { 3 * $i + 2 }
) {
return ( 0 );
}
}
 
return ( 1 );
}
 
/**
* No clue
* @param int $int
* @return string the char you meant?
*/
private function word($int) {
return ( chr($int & 0xFF) . chr(( $int >> 8 ) & 0xFF) );
}
 
/**
* Return the animated gif
* @return type
*/
function getAnimation() {
return $this->image;
}
 
/**
* Return the animated gif
* @return type
*/
function display() {
//late footer add
$this->addFooter();
header('Content-type:image/jpg');
echo $this->image;
}
 
}