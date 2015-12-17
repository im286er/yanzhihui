<?php
if(version_compare(PHP_VERSION,'5.3.0', '<')) die('require PHP > 5.3.0 !');
define('APP_PATH', './Manager/');      //定义项目路径
define('RUNTIME_PATH', '../Logs/Runtime/'); //定义Runtime路径
define('HTML_PATH', '../Html/');            //应用静态目录
define('APP_DEBUG', TRUE);                 //开启调试模式
require '../Public/ThinkPHP/ThinkPHP.php';
