<?php

//	Import the pYe
Yii::setPathOfAlias( 'pogostick', '/usr/local/psYiiExtensions/extensions/pogostick' );

//	Our configuration array
return array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name' => 'Yii/Pogostick Yii Extensions Blog',

	// preloading 'log' component
	'preload' => array('log'),

	//	Autoloading model and component classes
	'import' => array(
		'application.models.*',
		'application.components.*',
		'application.controllers.*',
		'pogostick.base.*',
		'pogostick.behaviors.*',
		'pogostick.commands.*',
		'pogostick.components.*',
		'pogostick.controllers.*',
		'pogostick.models.*',
		'pogostick.helpers.*',
		'pogostick.widgets.*',
		'pogostick.widgets.pagers.*',
		'pogostick.widgets.jqui.*',
	),

	//	application components
	'components' => array(
		'urlManager' => array(
			'urlFormat' => 'path',
			'showScriptName' => false,
       	),
       	
		//	Authentication manager...
		'authManager' => array(
			'class' => 'CDbAuthManager',
			'connectionID' => 'db',
		),

		'user' => array(
			// enable cookie-based authentication
			'allowAutoLogin' => false,
		),

		//      Database (Site)
		'db' => array(
			'class' => 'CDbConnection',
			'autoConnect' => true,
			'connectionString' => 'mysql:host=localhost;dbname=psYiiExtensions;',
			'username' => 'pye_user',
			'password' => 'pye_user',
			//'schemaCachingDuration' => 3600,
		),

		'log' => array(
			'class' => 'CLogRouter',
			'routes' => array(
				array(
					'class' => 'CFileLogRoute',
					'levels' => 'info, error, warning, trace',
					'maxFileSize' => '102400',
				),
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		'theme' => 'ui-lightness',
		'adminEmail' => 'webmaster@example.com',
		'@copyright' => 'Copyright &copy; 2009 My Company, LLC.',
		'@author' => 'Web Master <webmaster@example.com>',
		'@link' => 'http://www.example.com',
		'@package' => 'blog',
		'serverId' => $_SERVER['SERVER_ADDR'],							//	This server's ID for job queue
		'uploadPath' => '/protected/data/uploads',						//	The directory to which files are uploaded
	),
);
