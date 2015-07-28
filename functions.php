<?php


function animation_pause(){
    global $blank_image_data;
    $image_data_framed = add_frame_to_image_data($blank_image_data);
    $data = get_frame_from_image_data($image_data_framed,50);
    echo $data;
    flush_the_pipes();
    return $data;
}
function animate_image_data($options){
    $result = '';
    if(empty($options['type']))$options['type'] = 'slide_up';

    switch($options['type']){
        case 'slide_up':
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
            break;
        case 'fade_in':
            for($x=1;$x<100;$x+=10){
                $this_options = $options;
                $this_options['opacity'] = $x;
                $image_data = build_an_image($this_options);
                $image_data_framed = add_frame_to_image_data($image_data);
                $result .= get_frame_from_image_data($image_data_framed,8);
            }
            $image_data = build_an_image($options);
            $image_data_framed = add_frame_to_image_data($image_data);
            $result .= get_frame_from_image_data($image_data_framed,$options['pause']);

            break;
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

    $image_width = imagesx($im);
    $image_height = imagesy($im);
    // Get Bounding Box Size
    $text_box = imagettfbbox($font_size, 0, _ENVATO_LSG_FONT, $options['text']);
    // Get your Text Width and Height
    $text_width = $text_box[2] - $text_box[0];
    $text_height = $text_box[3] - $text_box[1];
    $y = ($image_height / 2) - ($text_height / 2);
    $y+=6;

    if(!empty($options['offset_y'])){
        $y += $options['offset_y'];
    }

    if(empty($options['icon'])) {
        // if there is no logo position the text in the middle of the image (http://stackoverflow.com/a/14517450/457850)
        // Calculate coordinates of the text
        $x = ($image_width / 2) - ($text_width / 2);
        $font_color = imagecolorallocate($im, 102, 102, 102);
        imagettftext($im, $font_size, 0, $x, $y, $font_color, _ENVATO_LSG_FONT, $options['text']);
    }else{
        // add our icon to the image.
        $icon = imagecreatefrompng($options['icon']);
        $icon_width = imagesx($icon);
        $icon_height = imagesy($icon);
        $icon_y = ($image_height - $icon_height) / 2;
        if(!empty($options['offset_y'])) {
            $icon_y += $options['offset_y'];
        }
        // we have a logo, put the text near it on the left.
        $x = 60;
        // build up a temporary icon/text image so we can apply alpha to it if needed
        $logo_and_text = imagecreatetruecolor($image_width, $image_height);
        imagealphablending($logo_and_text, false);
        imagesavealpha($logo_and_text, true);
        $trans_colour = imagecolorallocatealpha($logo_and_text, 0, 0, 0, 127);
        imagefill($logo_and_text, 0, 0, $trans_colour);
        imagecolortransparent($logo_and_text, $trans_colour);

        $font_color = imagecolorallocate($logo_and_text, 102, 102, 102);
        imagettftext($logo_and_text, $font_size, 0, $x, $y, $font_color, _ENVATO_LSG_FONT, $options['text']);
        imagecopy($logo_and_text, $icon, 10, $icon_y, 0, 0, $icon_width, $icon_height);
        imagecopymerge_alpha($im, $logo_and_text, 0, 0, 0, 0, $image_width, $image_height, isset($options['opacity']) ? $options['opacity'] : 100);
    }
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
//    $delay_in_hex = str_pad(base_convert($delay, 10, 16), 4, '0', STR_PAD_LEFT);
//    $hex_bits = str_split($delay_in_hex,2);
//    $binary = chr($delay); echo "delay $delay is $delay_in_hex and should be ".bin2hex($binary); print_r($hex_bits);exit;

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
//            $frame .= chr($delay); //delay?
//            $frame .= hex2bin('00');
            $delay_in_hex = str_pad(base_convert($delay, 10, 16), 4, '0', STR_PAD_LEFT);
            $hex_bits = str_split($delay_in_hex,2);
            $frame .= hex2bin($hex_bits[1]);
            $frame .= hex2bin($hex_bits[0]);
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
function imagecopymerge_alpha_old($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
    if(!isset($pct)){
        return false;
    }
    $pct /= 100;
    // Get image width and height
    $w = imagesx( $src_im );
    $h = imagesy( $src_im );
    // Turn alpha blending off
    imagealphablending( $src_im, false );
    // Find the most opaque pixel in the image (the one with the smallest alpha value)
    $minalpha = 127;
    for( $x = 0; $x < $w; $x++ )
        for( $y = 0; $y < $h; $y++ ){
            $alpha = ( imagecolorat( $src_im, $x, $y ) >> 24 ) & 0xFF;
            if( $alpha < $minalpha ){
                $minalpha = $alpha;
            }
        }
    //loop through image pixels and modify alpha for each
    for( $x = 0; $x < $w; $x++ ){
        for( $y = 0; $y < $h; $y++ ){
            //get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat( $src_im, $x, $y );
            $alpha = ( $colorxy >> 24 ) & 0xFF;
            //calculate new alpha
            if( $minalpha !== 127 ){
                $alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
            } else {
                $alpha += 127 * $pct;
            }
            //get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha( $src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
            //set pixel with the new color + opacity
            if( !imagesetpixel( $src_im, $x, $y, $alphacolorxy ) ){
                return false;
            }
        }
    }
    // The image copy
    imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
}


function filter_opacity( &$img, $opacity ) //params: image resource id, opacity in percentage (eg. 80)
{
    $opacity /= 100;

    //get image width and height
    $w = imagesx( $img );
    $h = imagesy( $img );

    //turn alpha blending off
    imagealphablending( $img, false );

    //find the most opaque pixel in the image (the one with the smallest alpha value)
    $minalpha = 127;
    for( $x = 0; $x < $w; $x++ )
        for( $y = 0; $y < $h; $y++ )
        {
            $alpha = ( imagecolorat( $img, $x, $y ) >> 24 ) & 0xFF;
            if( $alpha < $minalpha )
            { $minalpha = $alpha; }
        }

    //loop through image pixels and modify alpha for each
    for( $x = 0; $x < $w; $x++ )
    {
        for( $y = 0; $y < $h; $y++ )
        {
            //get current alpha value (represents the TANSPARENCY!)
            $colorxy = imagecolorat( $img, $x, $y );
            $alpha = ( $colorxy >> 24 ) & 0xFF;
            //calculate new alpha
            if( $minalpha !== 127 )
            { $alpha = 127 + 127 * $opacity * ( $alpha - 127 ) / ( 127 - $minalpha ); }
            else
            { $alpha += 127 * $opacity; }
            //get the color index with new alpha
            $alphacolorxy = imagecolorallocatealpha( $img, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
            //set pixel with the new color + opacity
            if( !imagesetpixel( $img, $x, $y, $alphacolorxy ) )
            { return false; }
        }
    }
    return true;
}

function envato_get_statement(){

    // now we start doing some calculations and build up additional gif frames to send to the browser.
    // these take long as we might have to do an API call or three
    require_once 'class.envato-basic.php';

    $current_statement = @json_decode(file_get_contents(_ENVATO_STATEMENT_CACHE_FILE),true);
    // when was the last time we got the statement?
    if(!$current_statement || filemtime(_ENVATO_STATEMENT_CACHE_FILE) < (time() - _ENVATO_STATEMENT_CACHE_TIMEOUT)){
        // grab the new statement from envato API
        // create a lock file so we don't do this at the same time.
        if(file_exists(_ENVATO_STATEMENT_CACHE_FILE.'.lock') && filemtime(_ENVATO_STATEMENT_CACHE_FILE.'.lock') < strtotime('-5 minutes') ){
            // lock file failed. remove it and start over
            @unlink(_ENVATO_STATEMENT_CACHE_FILE.'.lock');
        }
        if(!file_exists(_ENVATO_STATEMENT_CACHE_FILE.'.lock')) {
            touch(_ENVATO_STATEMENT_CACHE_FILE . '.lock');
            $current_statement = array();
            $envato = new envato_api_basic();
            $envato->set_personal_token(_ENVATO_PERSONAL_TOKEN);
            $data = $envato->api('v1/market/private/user/statement.json');
            if (!empty($data['statement'])) {
                file_put_contents(_ENVATO_STATEMENT_CACHE_FILE, json_encode($data['statement']));
                $current_statement = $data['statement'];
            }
            @unlink(_ENVATO_STATEMENT_CACHE_FILE.'.lock');
        }
    }
    return $current_statement;
}

function envato_get_item_ratings($item_id){

    // now we start doing some calculations and build up additional gif frames to send to the browser.
    // these take long as we might have to do an API call or three
    require_once 'class.envato-basic.php';

    $current_html = file_get_contents(_ENVATO_ITEM_CACHE_FILE);
    // when was the last time we got the statement?
    if(!$current_html || filemtime(_ENVATO_ITEM_CACHE_FILE) < (time() - _ENVATO_ITEM_CACHE_TIMEOUT)){
        // grab the new statement from envato API
        // create a lock file so we don't do this at the same time.
        if(file_exists(_ENVATO_ITEM_CACHE_FILE.'.lock') && filemtime(_ENVATO_ITEM_CACHE_FILE.'.lock') < strtotime('-5 minutes') ){
            // lock file failed. remove it and start over
            @unlink(_ENVATO_ITEM_CACHE_FILE.'.lock');
        }
        if(!file_exists(_ENVATO_ITEM_CACHE_FILE.'.lock')) {
            touch(_ENVATO_ITEM_CACHE_FILE . '.lock');
            $current_html = '';
            $envato = new envato_api_basic();
            $current_html = preg_replace('#\s+#',' ',$envato->get_url('http://themeforest.net/item/x/'.$item_id));
            file_put_contents(_ENVATO_ITEM_CACHE_FILE, $current_html);
            @unlink(_ENVATO_ITEM_CACHE_FILE.'.lock');
        }
    }
    $ratings = array();
    // we are looking for this HTML
    /* <span class="rating-breakdown__key">5 Star</span>
            <div class="rating-breakdown__meter">
              <div class="rating-breakdown__meter-bar">
                <div class="rating-breakdown__meter-progress" style="width: 80%">
                  80%
                </div>
              </div>
            </div>
            <span class="rating-breakdown__count">315</span> */
    if(preg_match('#meta itemprop="ratingCount" content="(\d+)"#',$current_html,$matches)){
        $ratings['total'] = $matches[1];
    }
    if(preg_match_all('#class="rating-breakdown__key">(\d) Star</span>.*class="rating-breakdown__count">(\d+)</span>#imsU',$current_html,$matches)){
        foreach($matches[1] as $key=>$val){
            $ratings[$val] = $matches[2][$key];
        }
    }
    return $ratings;
}