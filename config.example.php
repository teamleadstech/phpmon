<?php
//Basic config
define('APP_NAME', 'PHP Monitor');
define('APP_DOMAIN', 'www.phpmon.com');
define('APP_AUTHOR', 'Samuel Zhang');
define('SERVER_ID', 'gc-web-lab-vm1');

//Directory Config
define('SYSTEM_DIR', __DIR__);
define('API_DIR', SYSTEM_DIR.'/api/');
define('CLASS_DIR', SYSTEM_DIR.'/class/');
define('UPLOAD_DIR', SYSTEM_DIR.'/upload/');
define('PAGE_DIR', SYSTEM_DIR.'/page/');

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

?>

