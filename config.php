<?php


// This is the item name we are looking for in the API statement result:
$file_name = 'Ultimate Client Manager - CRM - Pro Edition';
// This is our personal token generated from build.envato.com
$personal_token = 'pemOG07IWPuvb8GCSum9lwvCVpkgHwj9';




set_time_limit(0);
ini_set('display_errors',true);
ini_set('error_reporting',E_ALL);
date_default_timezone_set('Australia/Melbourne');
define('_ENVATO_LSG_TEMPLATE','template_bg.png');
define('_ENVATO_LSG_TEMPLATE_MASK','template_mask.png');
define('_ENVATO_LSG_FONT',__DIR__.'/HelveticaNeue-Regular.ttf');
$statement_cache = 'cache_statement.json'; // change this name or block it with .htaccess if you don't want people to see your recent sales.
$statement_cache_timeout = 120; // please don't change this to anything lower. be nice to servers!
$viewer_cache = 'cache_viewers.json'; // change this name or block it with .htaccess if you don't want people to see info
$viewer_cache_timeout = 600; // how long to class someone as a visitor after they leave the page? default 10 minutes
$debug = isset($_REQUEST['debug']);
if(!file_exists($statement_cache)){
    touch($statement_cache);
}
if(!file_exists($statement_cache)){
    echo "Unable to create ".$statement_cache.". Please ensure correct permissions.";
    exit;
}
if(!file_exists($viewer_cache)){
    touch($viewer_cache);
}
if(!file_exists($viewer_cache)){
    echo "Unable to create ".$viewer_cache.". Please ensure correct permissions.";
    exit;
}