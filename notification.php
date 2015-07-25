<?php

@session_write_close();

set_time_limit(0);
ini_set('display_errors',true);
ini_set('error_reporting',E_ALL);
date_default_timezone_set('Australia/Brisbane');

$debug = false;
$base_image = 'template.png';
$base_image_blank = 'template_blank.png';
$font_file = __DIR__.'/Vera.ttf';
$statement_cache = '_statement.json';

$file_name = 'Ultimate Client Manager - CRM - Pro Edition'; // what to look for in the statement api result.
$timeout = 10; // how many seconds between sending the last image again.
$last_sent = false;
$statement_cache_timeout = 30; // seconds.
if(!file_exists($statement_cache)){
    touch($statement_cache);
}
$current_statement = array();
require_once 'class.envato-basic.php';

// build up a list of stats that we want to display to the user
$stats = array(
    'last_purchased' => array(
        'icon' => 'icon_cart.png',
        'text' => 'Last purchased x ago',
    ),
);


$current_statement = @json_decode(file_get_contents($statement_cache),true);

// base our generic stats off the cached statement then query the api for new data and then generate the updated stats.


// when was the last time we got the statement?
if(!$current_statement || filemtime($statement_cache) < (time() - $statement_cache_timeout)){
    // grab the new statement from envato API
    $current_statement = array();
    $envato = new envato_api_basic();
    $envato->set_personal_token('pemOG07IWPuvb8GCSum9lwvCVpkgHwj9');
    $data = $envato->api('v1/market/private/user/statement.json');
    if(!empty($data['statement'])){
        file_put_contents($statement_cache,json_encode($data['statement']));
        touch($statement_cache);
        if($debug)echo "GETTING NEW STATEMENT\n\n";
        $current_statement = $data['statement'];
    }
}


// Used to separate multipart
$boundary = "envatosalesgraphic";
// We start with the standard headers. PHP allows us this much
if(!$debug){
    header("Cache-Control: no-cache");
    header("Cache-Control: private");
    header("Pragma: no-cache");
    header("Content-type: multipart/x-mixed-replace; boundary=$boundary");
}

// send a blank image to start with:
for ($y = 1; $y < 3; $y++) {
    $im = imagecreatefrompng($base_image_blank);
    imagetruecolortopalette($im, true, 256);
    $white = imagecolorallocate($im, 255, 255, 255);
    imagecolortransparent($im, $white);
    ob_start();
    imagejpeg($im, null, 100);
    $last_image = ob_get_clean();
    if(!$debug)echo $last_image;
    imagedestroy($im);
    echo "--$boundary\n";
    // Per-image header, note the two new-lines
    echo "Content-type: image/jpeg\n\n";
    while (@ob_get_level()) @ob_end_flush();
    @flush();
    @ob_flush();
    $last_sent = time();
}
function prettyDate($time){
    $now = time();
    $ago = $now - $time;
    if($ago < 60){
        $when = round($ago);
        $s = ($when == 1)?"second":"seconds";
        return "$when $s ago";
    }elseif($ago < 3600){
        $when = round($ago / 60);
        $m = ($when == 1)?"minute":"minutes";
        return "$when $m ago";
    }elseif($ago >= 3600 && $ago < 86400){
        $when = round($ago / 60 / 60);
        $h = ($when == 1)?"hour":"hours";
        return "$when $h ago";
    }elseif($ago >= 86400 && $ago < 2629743.83){
        $when = round($ago / 60 / 60 / 24);
        $d = ($when == 1)?"day":"days";
        return "$when $d ago";
    }elseif($ago >= 2629743.83 && $ago < 31556926){
        $when = round($ago / 60 / 60 / 24 / 30.4375);
        $m = ($when == 1)?"month":"months";
        return "$when $m ago";
    }else{
        $when = round($ago / 60 / 60 / 24 / 365);
        $y = ($when == 1)?"year":"years";
        return "$when $y ago";
    }
}

$displayed_sale = false;
$last_sale = false;
while(true) {
    $current_statement = @json_decode(file_get_contents($statement_cache),true);
    // when was the last time we got the statement?
    if(!$current_statement || filemtime($statement_cache) < (time() - $statement_cache_timeout)){
        // grab the new statement from envato API
        $current_statement = array();
        $envato = new envato_api_basic();
        $envato->set_personal_token('pemOG07IWPuvb8GCSum9lwvCVpkgHwj9');
        $data = $envato->api('v1/market/private/user/statement.json');
        if(!empty($data['statement'])){
            file_put_contents($statement_cache,json_encode($data['statement']));
            touch($statement_cache);
            if($debug)echo "GETTING NEW STATEMENT\n\n";
            $current_statement = $data['statement'];
        }
    }
    foreach($current_statement as $item){
        if($debug){
            echo "Checking if ".$item['description']." matches '".$file_name."'\n";
        }
        if(!empty($item['kind']) && $item['kind'] == 'sale' && $item['description'] == $file_name){
            $last_sale = strtotime($item['occured_at']);
            break;
        }
    }
    if($last_sent < time() - $timeout){
        // send last image again.
        if(!$debug)echo $last_image;
        echo "--$boundary\n";
        // Per-image header, note the two new-lines
        echo "Content-type: image/jpeg\n\n";
        while (@ob_get_level()) @ob_end_flush();
        @flush();
        @ob_flush();
        $last_sent = time();
    }
    if($last_sale && $last_sale != $displayed_sale){
        $displayed_sale = $last_sale;
        for ($y = 1; $y < 3; $y++) {
            $im = imagecreatefrompng($base_image);
            imagetruecolortopalette($im, true, 256);
            $white = imagecolorallocate($im, 255, 255, 255);
            imagecolortransparent($im, $white);
            $font_color = imagecolorallocate($im, 102, 102, 102);
            if($debug)echo "Last purchased ".prettyDate($last_sale)."\n\n";
            imagettftext($im, 12, 0, 80, 43, $font_color, $font_file, "Last purchased ".prettyDate($last_sale));
            ob_start();
            imagejpeg($im, null, 100);
            $last_image = ob_get_clean();
            if(!$debug)echo $last_image;
            imagedestroy($im);

            echo "--$boundary\n";
            // Per-image header, note the two new-lines
            echo "Content-type: image/jpeg\n\n";
            while (@ob_get_level()) @ob_end_flush();
            @flush();
            @ob_flush();
            $last_sent = time();
        }
    }
    sleep(1);
    if($debug)echo 'Loop \n';
//    break;
}
