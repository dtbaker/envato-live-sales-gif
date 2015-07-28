<?php

/**
 * Simple Item Stats Image
 * Author: @dtbaker
 * Created: 25th July 2015
 * Website: http://github.com/dtbaker/envato-live-sales-gif/
 * If you improve this script with new images or better code please contribute them back to the github repo :) thanks!
 *
 */

include('config.php');
include('functions.php');

// now we start doing the fun stuff:

// first build up a quick "Loading" gif frame and send that to the browser as quickly as possible so it can render an empty image the correct size.
global $blank_image_data;
$blank_image_data = build_an_image(array(
    'text' => '',
    'icon' => false,
));

if(!_DTBAKER_DEBUG_MODE)header("Content-type: image/gif");
if(!_DTBAKER_DEBUG_MODE)echo get_animated_gif_header($blank_image_data); // send special "I'm an animating gif" header
$image_data_framed = add_frame_to_image_data($blank_image_data);
if(!_DTBAKER_DEBUG_MODE)echo get_frame_from_image_data($image_data_framed,2); // show "Loading" really quicky
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
    echo animate_image_data(array(
        'text' => 'Last purchased '.prettyDate($last_sale,' ago'),
        'icon' => 'icon_cart.png',
        'pause' => 200,
        'type' => 'fade_in',
    ));
}
flush_the_pipes();

echo ';';// end gif animation. commence loop.

