<?php
define('UM_PATH',realpath(dirname(__FILE__)));
define('UM_PLUGIN_PATH',UM_PATH.'/plugins/');
define('UM_DIRECTORY',basename(realpath(dirname(__FILE__))));
//define('UM_WEBPATH',''); // uncomment to override

define('UM_LANGUAGE','en');
define('UM_THEME','default');

define('LANG_CHARSET_MAIL_CONTENT_TYPE','text/plain');
define('LANG_CHARSET_CODE','iso-8859-1');

define('UM_DB_DRIVER','mysql');
define('UM_DB_HOST','localhost');
define('UM_DB_DATABASE','cerb4');
define('UM_DB_USER','cerberus');
define('UM_DB_PASS','cerberus');

?>