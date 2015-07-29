<?php

/**
 * Simple Item Stats Image
 * Author: @dtbaker
 * Created: 25th July 2015
 * Website: http://github.com/dtbaker/envato-live-sales-gif/
 * If you improve this script with new images or better code please contribute them back to the github repo :) thanks!
 *
 */

define('_ENVATO_LSG_TEMPLATE','template_305_bg.png');
define('_ENVATO_LSG_TEMPLATE_MASK','template_305_frame.png');

include('config.php');

$cache_gif_file = 'gif_currently_viewing.cache.'._ENVATO_ITEM_ID.'.gif';
if(file_exists($cache_gif_file) && filemtime($cache_gif_file) > time() - _GIF_CACHE_TIMEOUT && !isset($_REQUEST['refresh'])){
    header("Content-type: image/gif");
    if(!@readfile($cache_gif_file)){
        echo file_get_contents($cache_gif_content);
    }
    exit;
}


include('functions.php');

// now we start doing the fun stuff:

// first build up a quick "Loading" gif frame and send that to the browser as quickly as possible so it can render an empty image the correct size.
global $blank_image_data;
$blank_image_data = build_an_image(array(
    'text' => '',
    'icon' => false,
));

$cache_gif_content = '';

if(!_DTBAKER_DEBUG_MODE){
    header("Content-type: image/gif");
    $header = get_animated_gif_header($blank_image_data);
    $cache_gif_content .= $header;
    echo $header;
} // send special "I'm an animating gif" header
$image_data_framed = add_frame_to_image_data($blank_image_data);
if(!_DTBAKER_DEBUG_MODE){
    $first_frame = get_frame_from_image_data($image_data_framed,2);
    $cache_gif_content .= $first_frame;
    echo $first_frame;
    flush_the_pipes();
} // show "Loading" really quicky



// adding a really quick and dodgy "currently viewing" count to the stats:
$viewer_ip = $_SERVER['REMOTE_ADDR']; // todo - look for x_forward etc..
$viewer_database = @json_decode(file_get_contents(_VIEWER_CACHE_FILE),true);
if(!$viewer_database)$viewer_database = array();
$now = time();
$viewer_database[$viewer_ip] = $now;
foreach($viewer_database as $ip=>$time){
    if($time < $now - _VIEWER_CACHE_TIMEOUT){
        unset($viewer_database[$ip]);
    }
}
file_put_contents(_VIEWER_CACHE_FILE,json_encode($viewer_database));
// don't want to show "1 person viewing" that sounds lame.
$viewer_count = max(2,count($viewer_database));
$animate_image =  animate_image_data(array(
    'text' => $viewer_count.' people currently viewing',
    'icon' => 'icon_eye.png',
    'pause' => 2000,
    'type' => 'fade_in',
));
$cache_gif_content .= $animate_image;
echo $animate_image;


flush_the_pipes();

echo ';';// end gif animation. commence loop.
$cache_gif_content .= ';';

file_put_contents($cache_gif_file,$cache_gif_content);