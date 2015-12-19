<?php
// change the following paths if necessary
$yii=dirname(__FILE__).'/_framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
$app = Yii::createWebApplication($config);

$install_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . 'installation';

if(file_exists($install_file)) {
    if(!is_writable(dirname($install_file))) {
        throw new Exception('Directory ' . dirname($install_file) . ' have to be writable.');
    }

    if(!is_writable($install_file)) {
        throw new Exception('File ' . $install_file . ' have to be writable.');
    }

    Yii::app()->request->redirect('/install.php');
    Yii::app()->end();
}

$app->run();
