<?php


// This is the item ID we are using in API calls
if(!defined('_ENVATO_ITEM_ID'))define('_ENVATO_ITEM_ID', 2621629);
// or you can pass it in a url parameter like:  gif.php?item_id=12345
// if(!defined('_ENVATO_ITEM_ID'))define('_ENVATO_ITEM_ID', isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0);
// Your envato username
if(!defined('_ENVATO_USERNAME'))define('_ENVATO_USERNAME', 'dtbaker');
// This is our personal token generated from build.envato.com
if(!defined('_ENVATO_PERSONAL_TOKEN'))define('_ENVATO_PERSONAL_TOKEN',  'pemOG07IWPuvb8GCSum9lwvCVpkgHwj9');


set_time_limit(0);
ini_set('display_errors',true);
ini_set('error_reporting',E_ALL);
date_default_timezone_set('Australia/Melbourne');

if(!defined('_ENVATO_LSG_TEMPLATE'))define('_ENVATO_LSG_TEMPLATE','template_bg.png');
if(!defined('_ENVATO_LSG_TEMPLATE_MASK'))define('_ENVATO_LSG_TEMPLATE_MASK','template_mask.png');
define('_ENVATO_LSG_FONT',__DIR__.'/HelveticaNeue-Regular.ttf');
define('_ENVATO_STATEMENT_CACHE_FILE','cache_statement.json');
define('_ENVATO_STATEMENT_CACHE_TIMEOUT',600); // 10 minutues
define('_ENVATO_API_ITEM_CACHE_FILE','cache_api_item_'._ENVATO_ITEM_ID.'.json');
define('_ENVATO_API_ITEM_CACHE_TIMEOUT',3600); // 1 hour
define('_ENVATO_ITEM_CACHE_FILE','cache_item_'._ENVATO_ITEM_ID.'.json');
define('_ENVATO_ITEM_CACHE_TIMEOUT',3600); // 1 hour
define('_VIEWER_CACHE_FILE','cache_viewers_'._ENVATO_ITEM_ID.'.json');
define('_VIEWER_CACHE_TIMEOUT',600); // how long to class someone as a visitor after they leave the page? default 10 minutes
define('_GIF_CACHE_TIMEOUT',30); // how long to keep static gif images cached
define('_DTBAKER_DEBUG_MODE',false); // this doesn't do much

foreach(array(_ENVATO_STATEMENT_CACHE_FILE, _ENVATO_API_ITEM_CACHE_FILE, _ENVATO_ITEM_CACHE_FILE, _VIEWER_CACHE_FILE) as $file){
    if(!file_exists($file)){
        touch($file);
    }
    if(!file_exists($file)){
        echo "Unable to create ".$file.". Please ensure correct permissions.";
        exit;
    }
}