<?php
//Basic config
define('APP_NAME', 'PHP Monitor');
define('APP_DOMAIN', 'www.phpmon.com');
define('APP_AUTHOR', 'Samuel Zhang');
define('DEBUG_MODE', true);

//Node Config
define('SERVER_ID', '');
define('SERVER_KEY', '');

//Directory Config
define('SYSTEM_DIR', __DIR__);
define('CLASS_DIR', SYSTEM_DIR.'/class/');
define('CRON_DIR', SYSTEM_DIR.'/cron/');
define('WWW_DIR', SYSTEM_DIR.'/www/');

//Database Config
define('MYSQL_HOST', '');
define('MYSQL_USER', '');
define('MYSQL_PASSWD', '');
define('MYSQL_DBNAME', '');

//Maigun Config(Email Service)
define('MAILGUN_APIKEY', '');
define('MAILGUN_BASEURL', '');

//Twilio Config(Text Service)
define('TWILIO_SID', '');
define('TWILIO_TOKEN', '');
define('TWILIO_NUM', '');

//Timezone
date_default_timezone_set('UTC');

//Autoload
function loadClassDependency($class)
{
    include_once CLASS_DIR.$class.'.class.php';
}

spl_autoload_register('loadClassDependency');

if(DEBUG_MODE){
	ini_set("display_errors", "1");
	error_reporting(E_ALL);
}else{
	ini_set("display_errors", "0");
	error_reporting(0);
}

