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

$current_statement = array();

// now we start doing the fun stuff:

// first build up a quick "Loading" gif frame and send that to the browser as quickly as possible so it can render an empty image the correct size.
global $blank_image_data;
$blank_image_data = build_an_image(array(
    'text' => '',
    'icon' => false,
));

if(!$debug)header("Content-type: image/gif");
if(!$debug)echo get_animated_gif_header($blank_image_data); // send special "I'm an animating gif" header
$image_data_framed = add_frame_to_image_data($blank_image_data);
if(!$debug)echo get_frame_from_image_data($image_data_framed,2); // show "Loading" really quicky
flush_the_pipes();

//sleep(4);

// adding a really quick and dodgy "currently viewing" count to the stats:
$viewer_ip = $_SERVER['REMOTE_ADDR']; // todo - look for x_forward etc..
$viewer_database = @json_decode(file_get_contents($viewer_cache),true);
if(!$viewer_database)$viewer_database = array();
$now = time();
$viewer_database[$viewer_ip] = $now;
foreach($viewer_database as $ip=>$time){
    if($time < $now - $viewer_cache_timeout){
        unset($viewer_database[$ip]);
    }
}
file_put_contents($viewer_cache,json_encode($viewer_database));
if(count($viewer_database) > 1){
    // don't want to show "1 person viewing" that sounds lame.
    echo animate_image_data(array(
        'text' => count($viewer_database).' People Currently Viewing',
        'icon' => 'icon_eye.png',
        'pause' => 200,
    ));
    flush_the_pipes();
    animation_pause();
    flush_the_pipes();
}


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
if($debug){
//    print_r($current_statement); exit;
}
foreach($current_statement as $item){
    if(!empty($item['kind']) && $item['kind'] == 'sale' && $item['description'] == $file_name){
        if($debug){
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
    ));
    flush_the_pipes();
}
animation_pause();
flush_the_pipes();

if($first_sale && $sale_count && $first_sale != $last_sale) {
    echo animate_image_data(array(
        'text' => $sale_count.' purchases in the last '.prettyDate($first_sale,''),
        'icon' => 'icon_trending.png',
        'pause' => 200,
    ));
    flush_the_pipes();
}

animation_pause();
flush_the_pipes();

echo ';';// end gif animation. commence loop.

