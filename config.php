<?php


// This is the item name we are looking for in the API statement result:
if(!defined('_ENVATO_ITEM_NAME'))define('_ENVATO_ITEM_NAME', 'Ultimate Client Manager - CRM - Pro Edition');
// This is the item ID we are using in API calls
if(!defined('_ENVATO_ITEM_ID'))define('_ENVATO_ITEM_ID', 2621629);
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
define('_ENVATO_STATEMENT_CACHE_TIMEOUT',600);
define('_ENVATO_ITEM_CACHE_FILE','cache_item_'._ENVATO_ITEM_ID.'.json');
define('_ENVATO_ITEM_CACHE_TIMEOUT',600);
define('_VIEWER_CACHE_FILE','cache_viewers.json');
define('_VIEWER_CACHE_TIMEOUT',600); // how long to class someone as a visitor after they leave the page? default 10 minutes
define('_GIF_CACHE_TIMEOUT',30); // how long to keep static gif images cached
define('_DTBAKER_DEBUG_MODE',isset($_REQUEST['debug']));

if(!file_exists(_ENVATO_STATEMENT_CACHE_FILE)){
    touch(_ENVATO_STATEMENT_CACHE_FILE);
}
if(!file_exists(_ENVATO_STATEMENT_CACHE_FILE)){
    echo "Unable to create "._ENVATO_STATEMENT_CACHE_FILE.". Please ensure correct permissions.";
    exit;
}
if(!file_exists(_VIEWER_CACHE_FILE)){
    touch(_VIEWER_CACHE_FILE);
}
if(!file_exists(_VIEWER_CACHE_FILE)){
    echo "Unable to create "._VIEWER_CACHE_FILE.". Please ensure correct permissions.";
    exit;
}