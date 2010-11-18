<?php

//	Import the pYe
Yii::setPathOfAlias( 'pogostick', '/usr/local/psYiiExtensions/extensions/pogostick' );

//	Our configuration array
return array(
	'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name' => 'Yii/Pogostick Yii Extensions Blog',
	'defaultController' => 'post',

	// preload components
	'preload' => array(
		'log',
	),

	//	Autoloading model and component classes
	'import' => array(
		'application.models.*',
		'application.components.*',
		'pogostick.base.*',
		'pogostick.behaviors.*',
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
			'rules' => array(
				'tag/<tag>' => 'post/list',
				'date/<date>' => 'post/list',
				'posts' => 'post/list',
				'post/<id:\d+>' => 'post/show',
				'post/update/<id:\d+>' => 'post/update',
			),
       	),
       	
		//	Authentication manager...
		'authManager' => array(
			'class' => 'CDbAuthManager',
			'connectionID' => 'db',
		),

		'user' => array(
			//	Enable cookie-based authentication
			'allowAutoLogin' => false,
			//	Force 403 HTTP error if authentication needed
			'loginUrl' => null,
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

	//	Our application parameters
	'params' => require( dirname( __FILE__ ) . '/params.php' ),

);
