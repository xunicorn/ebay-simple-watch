<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',

	'name'=>'Ebay Simple Watching',
    //'app_email' => 'test@mail.kom',

	// preloading 'log' component
	'preload'=>array('log', 'booster', 'MultiMailer'),

    'aliases' => array(
        'booster' => 'application.vendors.yiibooster',
        //'ext'     => 'application.extensions',
    ),
	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.models.forms.*',
		'application.components.*',
		'application.components.ebayAPI.*',
		'application.models.ebayAPI.*',
		'application.vendors.UserFunctions',
		'application.vendors.MailHelper',
        'booster.helpers.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool

		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'qqq',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
            'generatorPaths' => array(
                'bootstrap.gii',
            ),
		),

	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
        'booster' => array(
            'class' => 'booster.components.Bootstrap',
        ),
        'MultiMailer' => array(
            'class' => 'application.extensions.MultiMailer.MultiMailer',
        ),
        /*
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=ebay',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => 'root',
			'charset' => 'utf8',
		),
        */

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),

                array(
                    'class'   => 'CProfileLogRoute',
                    'enabled' => YII_DEBUG,
                ),
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
        'reserveLogin' => false,
	),
);