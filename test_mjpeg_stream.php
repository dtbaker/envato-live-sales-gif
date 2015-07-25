<?php

set_time_limit(0);
//ini_set('display_errors',true);
//ini_set('error_reporting',E_ALL);

$base_image = 'template.png';
$base_image_blank = 'template_blank.png';
$font_file = __DIR__.'/Vera.ttf';


// Used to separate multipart
$boundary = "envatosales";
// We start with the standard headers. PHP allows us this much
header("Cache-Control: no-cache");
header("Cache-Control: private");
header("Pragma: no-cache");
header("Content-type: multipart/x-mixed-replace; boundary=$boundary");
$last_text = '';
while(true) {
    $text = file_get_contents('test_text.txt');
    if($text != $last_text) {
        for ($y = 1; $y < 3; $y++) {
            $im = imagecreatefrompng($base_image);
            imagetruecolortopalette($im, true, 256);
            $white = imagecolorallocate($im, 255, 255, 255);
            imagecolortransparent($im, $white);
            $font_color = imagecolorallocate($im, 102, 102, 102);
            imagettftext($im, 15, 0, 80, 43, $font_color, $font_file, $text);
            imagejpeg($im, null, 100);
            imagedestroy($im);

            echo "--$boundary\n";
            // Per-image header, note the two new-lines
            echo "Content-type: image/jpeg\n\n";
        }
    }
    sleep(1);
}
