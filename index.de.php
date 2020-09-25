<?php
//dezend by http://www.yunlu99.com/ QQ:270656184
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
	exit('require PHP > 5.3.0 !');
}

define('APP_DEBUG', true);
define('APP_PATH', './Application/');
header('Content-type:text/html;charset=utf-8');
error_reporting(1 | 2 | 4);
require '../ThinkPHP/ThinkPHP.php';

?>
