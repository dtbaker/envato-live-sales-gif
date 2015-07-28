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

$cache_gif_file = 'gif_last_purchased.cache.gif';
if(file_exists($cache_gif_file) && filemtime($cache_gif_file) > time() - _GIF_CACHE_TIMEOUT && !isset($_REQUEST['refresh'])){
    header("Content-type: image/gif");
    readfile($cache_gif_file);
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
} // show "Loading" really quicky
flush_the_pipes();


$first_sale = $last_sale = false;
$sale_count = 0;
$current_statement = envato_get_statement();

foreach($current_statement as $item){
    if(!empty($item['kind']) && $item['kind'] == 'sale' && $item['description'] == _ENVATO_ITEM_NAME){
        if(_DTBAKER_DEBUG_MODE){
            echo "Found a match.";
            print_r($item);
        }
        if(!$last_sale)$last_sale = strtotime($item['occured_at']);
        $first_sale = strtotime($item['occured_at']);
        $sale_count++;
    }
}
if($last_sale){
    $animate_image = animate_image_data(array(
        'text' => 'Last purchased '.prettyDate($last_sale,' ago'),
        'icon' => 'icon_cart.png',
        'pause' => 2000,
        'type' => 'fade_in',
    ));
    $cache_gif_content .= $animate_image;
    echo $animate_image;
}
flush_the_pipes();

echo ';';// end gif animation. commence loop.
$cache_gif_content .= ';';

file_put_contents($cache_gif_file,$cache_gif_content);