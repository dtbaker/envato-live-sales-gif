<?php


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
    $animated_extension .= hex2bin('00');// number of times to loop. 0 infinite.
    $animated_extension .= hex2bin('00');
    $animated_extension .= hex2bin('00');
    $new_header = $first_13 . $animated_extension;

    header("Content-type: image/gif");
    echo $new_header;

    // send first frame:
    echo get_frame_from_image_data($contents, 1);
    echo get_frame_from_image_data($contents, 10);
    flush();
    ob_flush();


    $last_text = '';
    while(true){
        $new_text = file_get_contents('text.txt');
        if($new_text != $last_text){
            $last_text = $new_text;


            $im = imagecreatefrompng($base_image);
            imagetruecolortopalette($im, true, 256);
            $white = imagecolorallocate($im, 255, 255, 255);
            imagecolortransparent($im, $white);
            $font_color = imagecolorallocate($im, 102, 102, 102);
            imagettftext($im, 15, 0, 80, 43, $font_color, $font_file, $new_text);
            ob_start();
            imagegif($im);
            $contents = ob_get_clean();
            imagedestroy($im);

            echo get_frame_from_image_data($contents, 100);
            flush();
            ob_flush();

            sleep(4);


        }
    }

}
