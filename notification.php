<?php
ini_set('display_errors',true);
ini_set('error_reporting',E_ALL);


$base_image = 'template.png';
$correct_gif_header = 'correct.gif';


$im = imagecreatefrompng($base_image);
imagetruecolortopalette($im, true, 256);
$white = imagecolorallocate($im, 255, 255, 255);
imagecolortransparent($im, $white);
$font_color = imagecolorallocate($im, 102, 102, 102);
$font_file = __DIR__.'/Vera.ttf';
$text = 'Testing...';
imagettftext($im, 15, 0, 80, 43, $font_color, $font_file, $text);
ob_start();
imagegif($im);
$contents = ob_get_clean();
imagedestroy($im);

function get_frame_from_image_data($contents, $delay){
    $frame = '';

    if(strpos($contents,'GIF89a') === 0){
        $first_13 = substr($contents,0,13);
        // find out teh size of our global colour table.
        $packed = base_convert(ord($first_13[10]),10,2);
        $global_color = substr($packed,1,3);
        $global_color_number = (int)base_convert($global_color,2,10);
        $global_color_count = pow(2, $global_color_number+1);
        $global_color_bytes = 3 * $global_color_count;
        // check if our gif has an extension already.
        $image_start = 13 + $global_color_bytes;
        if(bin2hex($contents[$image_start]) == '21'){
            // our gif has an extension, find out how long it is.
            $size = (int)base_convert(bin2hex($contents[$image_start + 2]),16,10);
            $image_start = $image_start + $size + 4;
        }
        if(bin2hex($contents[$image_start]) == '2c') {
            // found the image desc:10 bytes
            $color_table = substr($contents, 13, $global_color_bytes);
            $image_descriptor = substr($contents, $image_start, 9);
            $new_image_descriptor = $image_descriptor . chr(base_convert('10000' . $global_color, 2, 10));

            $data = substr($contents, $image_start + 10);
            $data = substr($data, 0, strlen($data) - 1);

            $frame = '';
            $frame .= hex2bin('21');
            $frame .= hex2bin('f9');
            $frame .= hex2bin('04');
            $frame .= hex2bin('04');
            $frame .= chr($delay); //delay?
            $frame .= hex2bin('00');
            $frame .= hex2bin('1f');
            $frame .= hex2bin('00');
            $frame .= $new_image_descriptor;
            $frame .= $color_table;
            $frame .= $data;
        }
    }
    return $frame;
}

if(strpos($contents,'GIF89a') === 0){
    // correct GIF
    /*$hex_ary = array();
    foreach (str_split($contents) as $chr) {
        $hex_ary[] = sprintf("%02X", ord($chr));
    }
    print_r($hex_ary);*/

    /*
     * header: 6
     * logical screen descriptor: 7 (width and height)
     * global color table: (calculated based on logical screen desctirotp)
     * graphics control extension: 8 bytes
     * image descriptor: 10 bytes
     *
     */

    $first_13 = substr($contents,0,13);
    // find out teh size of our global colour table.
    $packed = base_convert(ord($first_13[10]),10,2);
    $global_color = substr($packed,1,3);
    $global_color_number = (int)base_convert($global_color,2,10);
    $global_color_count = pow(2, $global_color_number+1);
    $global_color_bytes = 3 * $global_color_count;;
    $first_13[10] = chr(0); // reset packed field
    $animated_extension = '';
    $animated_extension .= hex2bin('21');
    $animated_extension .= hex2bin('ff');
    $animated_extension .= hex2bin('0b');
    $animated_extension .= 'NETSCAPE2.0';
    $animated_extension .= hex2bin('03');
    $animated_extension .= hex2bin('01');
    $animated_extension .= hex2bin('00');
    $animated_extension .= hex2bin('00');
    $animated_extension .= hex2bin('00');
    $new_header = $first_13 . $animated_extension;

    header("Content-type: image/gif");
    echo $new_header;

    echo get_frame_from_image_data($contents, 50);
    flush();
    ob_flush();

    sleep(4);

    $im = imagecreatefrompng($base_image);
    imagetruecolortopalette($im, true, 256);
    $white = imagecolorallocate($im, 255, 255, 255);
    imagecolortransparent($im, $white);
    $font_color = imagecolorallocate($im, 102, 102, 102);
    $font_file = __DIR__.'/Vera.ttf';
    $text = 'Testing testing testing...';
    imagettftext($im, 15, 0, 80, 43, $font_color, $font_file, $text);
    ob_start();
    imagegif($im);
    $contents = ob_get_clean();
    imagedestroy($im);

    echo get_frame_from_image_data($contents, 5);
    flush();
    ob_flush();

}

exit;
// we use this Gif Animation class: https://github.com/lunakid/AnimGif

require_once 'AnimGif.php';


$frames = array(
    imagecreatefrompng("template.png"),
    imagecreatefrompng("template2.png"),
    imagecreatefrompng("template3.png"),
);
$durations = array(10);

$anim = new GifCreator\AnimGif();
$anim->create($frames, $durations, 0);


header("Content-type: image/gif");
echo $anim->get();

// process:
/**
 *  pass in1.gif through full_gif_to_animated_gif_header() and create 0.part
 *      gif should start with GIF89a
 *      get the first 13 characters of the GIF string
 *      replace the 10th character with chr(0)
 *      ANIMATED_GIF_EXTENSION = hex_to_binary("21 ff 0b") + "NETSCAPE2.0" + hex_to_binary("03 01 01 00 00")
 *      assert len(ANIMATED_GIF_EXTENSION) == 19
 *      return logical_description + ANIMATED_GIF_EXTENSION
 *
 * run create_frame_file() on each of the remaining gifs
 * run each gif through full_gif_to_frame()
 *      ensure gif starts with GIF89a
 *      # Global color table
assert hex(ord(raw_gif[10])) == "0xf7"
gct_len = 256
gct_range = 13, 13 + (gct_len * 3)
color_table = raw_gif[gct_range[0]:gct_range[1]]

# Image descriptor
image_descriptor_range = gct_range[1], gct_range[1] + 10
image_descriptor = raw_gif[image_descriptor_range[0] : image_descriptor_range[1]]
assert image_descriptor[0] == ","
assert ord(image_descriptor[-1]) == 0        # Packed Fields
packed_fields = chr(int("10000111", base=2)) # Local Color Table Flag        1 Bit
# Interlace Flag                1 Bit
# Sort Flag                     1 Bit
# Reserved                      2 Bits
# Size of Local Color Table     3 Bits

new_image_descriptor = image_descriptor[:-1] + packed_fields

# Image data
data_range = image_descriptor_range[1], len(raw_gif) - 1
data = raw_gif[data_range[0] : data_range[1]]
assert raw_gif[data_range[1]] == ";" # file terminator

# New frame data
frame = hex_to_binary("21 f9 04 04") + chr(frame_interval) + hex_to_binary("00 1f 00")
frame += new_image_descriptor
frame += color_table
frame += data
 *
 */