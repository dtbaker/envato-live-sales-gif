<?php

/**
 * Simple Item Stats Image
 * Author: @dtbaker
 * Created: 25th July 2015
 * Website: http://github.com/dtbaker/envato-live-sales-gif/
 * If you improve this script with new images or better code please contribute them back to the github repo :) thanks!
 *
 */


// This is the item name we are looking for in the API statement result:
$file_name = 'Ultimate Client Manager - CRM - Pro Edition';
// This is our personal token generated from build.envato.com
$personal_token = 'pemOG07IWPuvb8GCSum9lwvCVpkgHwj9';



set_time_limit(0);
ini_set('display_errors',true);
ini_set('error_reporting',E_ALL);
date_default_timezone_set('Australia/Melbourne');
$debug = false;
define('_ENVATO_LSG_TEMPLATE','template_bg.png');
define('_ENVATO_LSG_TEMPLATE_MASK','template_mask.png');
define('_ENVATO_LSG_FONT',__DIR__.'/HelveticaNeue-Regular.ttf');
$statement_cache = 'cache_statement.json'; // change this name or block it with .htaccess if you don't want people to see your recent sales.
$statement_cache_timeout = 120; // please don't change this to anything lower. be nice to servers!
if(!file_exists($statement_cache)){
    touch($statement_cache);
}
$current_statement = array();


// now we start doing the fun stuff:

// first build up a quick "Loading" gif frame and send that to the browser as quickly as possible so it can render an empty image the correct size.
$blank_image_data = build_an_image(array(
    'text' => '',
    'icon' => false,
));

header("Content-type: image/gif");
echo get_animated_gif_header($blank_image_data); // send special "I'm an animating gif" header
$image_data_framed = add_frame_to_image_data($blank_image_data);
echo get_frame_from_image_data($image_data_framed,2); // show "Loading" really quicky
flush_the_pipes();

//sleep(4);

// now we start doing some calculations and build up additional gif frames to send to the browser.
// these take long as we might have to do an API call or three
require_once 'class.envato-basic.php';

$first_sale = $last_sale = false;
$sale_count = 0;
$current_statement = @json_decode(file_get_contents($statement_cache),true);
// when was the last time we got the statement?
if(!$current_statement || filemtime($statement_cache) < (time() - $statement_cache_timeout)){
    // grab the new statement from envato API
    $current_statement = array();
    $envato = new envato_api_basic();
    $envato->set_personal_token($personal_token);
    $data = $envato->api('v1/market/private/user/statement.json');
    if(!empty($data['statement'])){
        file_put_contents($statement_cache,json_encode($data['statement']));
        touch($statement_cache);
        $current_statement = $data['statement'];
    }
}
foreach($current_statement as $item){
    if(!empty($item['kind']) && $item['kind'] == 'sale' && $item['description'] == $file_name){
        if(!$last_sale)$last_sale = strtotime($item['occured_at']);
        $first_sale = strtotime($item['occured_at']);
        $sale_count++;
    }
}
if($last_sale){
    echo animate_image_data(array(
        'text' => 'Last purchased '.prettyDate($last_sale,' ago'),
        'icon' => 'icon_cart.png',
        'pause' => 200,
    ));
    flush_the_pipes();
}
// pause:
$image_data_framed = add_frame_to_image_data($blank_image_data);
echo get_frame_from_image_data($image_data_framed,50);
flush_the_pipes();

if($first_sale && $sale_count && $first_sale != $last_sale) {
    echo animate_image_data(array(
        'text' => $sale_count.' purchases in the last '.prettyDate($first_sale,''),
        'icon' => 'icon_trending.png',
        'pause' => 200,
    ));
    flush_the_pipes();
}


echo ';';// end gif animation. commence loop.


function animate_image_data($options){
    $result = '';
    $frame_inc = 0.1;

    $move_by = 50;
    $move_pixels = 2;
    $frame_delay = 2;
    for($y = $move_by; $y > 0; $y-=$move_pixels){
        $this_options = $options;
        $this_options['offset_y'] = $y;
        //$this_options['text'] .=  ' ('.$frame_delay.')';
        $image_data = build_an_image($this_options);
        $image_data_framed = add_frame_to_image_data($image_data);
        $result .= get_frame_from_image_data($image_data_framed,floor($frame_delay));
        $frame_delay = $frame_delay + $frame_inc;
    }
    $this_options = $options;
    $this_options['offset_y'] = 0;
    //$this_options['text'] .=  ' ('.$frame_delay.')';
    $image_data = build_an_image($this_options);
    $image_data_framed = add_frame_to_image_data($image_data);
    $result .= get_frame_from_image_data($image_data_framed,$options['pause']);

    $move_by = 50;
    $move_pixels = 2;
    for($y = 0; $y > -$move_by; $y-=$move_pixels){
        $this_options = $options;
        $this_options['offset_y'] = $y;
        //$this_options['text'] .=  ' ('.$frame_delay.')';
        $image_data = build_an_image($this_options);
        $image_data_framed = add_frame_to_image_data($image_data);
        $result .= get_frame_from_image_data($image_data_framed,ceil($frame_delay));
        $frame_delay = $frame_delay - $frame_inc;
    }
    return $result;
}
function add_frame_to_image_data($image_data,$offset_y=0){
    $bg = imagecreatefromstring($image_data);
    $mask = imagecreatefrompng(_ENVATO_LSG_TEMPLATE_MASK);
    imagecopymerge_alpha($bg, $mask, 0, 0, 0, 0, imagesx($mask), imagesy($mask), 100);
    ob_start();
    imagegif($bg);
    return ob_get_clean();
}
/**
 *
 * Takes some simply options and builds up an image using php gd functions
 *
 * @param $options
 * @return string raw gif image data
 */
function build_an_image($options){

    $font_size = 12;
    $im = imagecreatefrompng(_ENVATO_LSG_TEMPLATE);
    imagetruecolortopalette($im, true, 256);
    $white = imagecolorallocate($im, 255, 255, 255);
    imagecolortransparent($im, $white);
    $font_color = imagecolorallocate($im, 102, 102, 102);

    $image_width = imagesx($im);
    $image_height = imagesy($im);
    // Get Bounding Box Size
    $text_box = imagettfbbox($font_size, 0, _ENVATO_LSG_FONT, $options['text']);
    // Get your Text Width and Height
    $text_width = $text_box[2] - $text_box[0];
    $text_height = $text_box[3] - $text_box[1];
    $y = ($image_height / 2) - ($text_height / 2);
    $y+=5;

    if(!empty($options['offset_y'])){
        $y += $options['offset_y'];
    }

    if(empty($options['icon'])) {
        // if there is no logo position the text in the middle of the image (http://stackoverflow.com/a/14517450/457850)
        // Calculate coordinates of the text
        $x = ($image_width / 2) - ($text_width / 2);
    }else{
        // add our icon to the image.
        $icon = imagecreatefrompng($options['icon']);
        $icon_y = 10;
        if(!empty($options['offset_y'])) {
            $icon_y += $options['offset_y'];
        }
        imagecopymerge_alpha($im, $icon, 10, $icon_y, 0, 0, imagesx($icon), imagesy($icon), 100);
        // we have a logo, put the text near it on the left.
        $x = 80;
    }
    imagettftext($im, $font_size, 0, $x, $y, $font_color, _ENVATO_LSG_FONT, $options['text']);
    ob_start();
    imagegif($im);
    $image_data = ob_get_clean();
    imagedestroy($im);
    return $image_data;
}


/**
 *
 * Builds up a new "animated" gif header based on a provided static gif image
 * This lets us send the first frame to the browser while we generate our other frames
 *
 * @param $contents - raw gif data from imagegif() output
 */
function get_animated_gif_header($contents) {
    if (strpos($contents, 'GIF89a') === 0) {

        /*
         * header: 6
         * logical screen descriptor: 7 (width and height)
         * global color table: (calculated based on logical screen desctirotp)
         * graphics control extension: 8 bytes
         * image descriptor: 10 bytes
         */

        $first_13 = substr($contents, 0, 13);
        // find out teh size of our global colour table.
        $packed = base_convert(ord($first_13[10]), 10, 2);
        $global_color = substr($packed, 1, 3);
        $global_color_number = (int)base_convert($global_color, 2, 10);
        $global_color_count = pow(2, $global_color_number + 1);
        $global_color_bytes = 3 * $global_color_count;
        $first_13[10] = chr(0); // reset packed field
        $animated_extension = '';
        $animated_extension .= hex2bin('21');
        $animated_extension .= hex2bin('ff');
        $animated_extension .= hex2bin('0b');
        $animated_extension .= 'NETSCAPE2.0';
        $animated_extension .= hex2bin('03');
        $animated_extension .= hex2bin('01');
        $animated_extension .= hex2bin('00');// number of times to loop. 0 infinite.
        $animated_extension .= hex2bin('00');
        $animated_extension .= hex2bin('00');
        return $first_13 . $animated_extension;
    }
    return false;
}


/**
 * This grabs the raw header and image data out of a static GIF image.
 * Formats this header/data in a way that can be appended to a streaming GIF animation.
 * Yes this can be simplified! I spent a lot of time digging around the GIF header offsets to get it right. Someone please make this more awesome.
 * Inspiration from this python script: https://github.com/jbochi/gifstreaming/blob/master/transform.py
 *
 * @param $contents - raw gif data from imagegif() output
 * @param $delay - number of seconds to show this frame in the animation
 *
 */
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

function flush_the_pipes(){
    while (@ob_get_level()) @ob_end_flush();
    @flush();
    @ob_flush();
}

// copied from stackoverflow somewhere.
function prettyDate($time,$suffix=' ago'){
    $now = time();
    $ago = $now - $time;
    if($ago < 60){
        $when = round($ago);
        $s = ($when == 1)?"second":"seconds";
        return "$when $s".$suffix;
    }elseif($ago < 3600){
        $when = round($ago / 60);
        $m = ($when == 1)?"minute":"minutes";
        return "$when $m".$suffix;
    }elseif($ago >= 3600 && $ago < 86400){
        $when = round($ago / 60 / 60);
        $h = ($when == 1)?"hour":"hours";
        return "$when $h".$suffix;
    }elseif($ago >= 86400 && $ago < 2629743.83){
        $when = round($ago / 60 / 60 / 24);
        $d = ($when == 1)?"day":"days";
        return "$when $d".$suffix;
    }elseif($ago >= 2629743.83 && $ago < 31556926){
        $when = round($ago / 60 / 60 / 24 / 30.4375);
        $m = ($when == 1)?"month":"months";
        return "$when $m".$suffix;
    }else{
        $when = round($ago / 60 / 60 / 24 / 365);
        $y = ($when == 1)?"year":"years";
        return "$when $y".$suffix;
    }
}

// from: http://php.net/manual/en/function.imagecopymerge.php
function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
    // creating a cut resource
    $cut = imagecreatetruecolor($src_w, $src_h);

    // copying relevant section from background to the cut resource
    imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);

    // copying relevant section from watermark to the cut resource
    imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);

    // insert cut resource to destination image
    imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
}
