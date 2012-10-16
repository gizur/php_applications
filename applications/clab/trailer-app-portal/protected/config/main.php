<?php
// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Coop Trailer App',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.extensions.*'
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'anil',
		 	// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
	*/	
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format
		/*
				'urlManager'=>array(
                    'urlFormat'=>'path',
                    'rules'=>array(
                    'vtentity/<module:'.URL_MATCH.'>'=>'vtentity/index',
                     'vtentity/<module:'.URL_MATCH.'>/<action:'.URL_MATCH.'>'=>'vtentity/<action>',
                     'vtentity/<module:'.URL_MATCH.'>/<action:'.URL_MATCH.'>/<id:'.URL_MATCH.'>'=>'vtentity/<action>',
                    'vtentity/<module:'.URL_MATCH.'>/list/<id:'.URL_MATCH.'>/dvcpage/<dvcpage:'.URL_MATCH.'>'=>'vtentity/list',
                    // Faq module specific class
					'vtentity/<module:Faq>'=>'faq/index',
					'vtentity/<module:Faq>/<action:'.URL_MATCH.'>'=>'faq/<action>',
					'vtentity/<module:Faq>/<action:'.URL_MATCH.'>/<id:'.URL_MATCH.'>'=>'faq/<action>',
					'vtentity/<module:Faq>/list/<id:'.URL_MATCH.'>/dvcpage/<dvcpage:'.URL_MATCH.'>'=>'faq/list',
										),
		), */
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		// uncomment the following to use a MySQL database
		/*
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=testdrive',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
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
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
                'protocol'=>'http://',            
		'GIZURCLOUD_SECRET_KEY' => '9b45e67513cb3377b0b18958c4de55be',
		'GIZURCLOUD_API_KEY' => 'GZCLDFC4B35B',
		'API_VERSION' => '0.1',
		'URL' => 'http://phpapplications-env-sixmtjkbzs.elasticbeanstalk.com/api/index.php/api/',
                'loggable_account' => 'ACC1',
		'language' => array(
	                 'en' => 'English',
	                 'sv' => 'Swedish'
		)
	),
	// Default vtentity behaviour
	//http://gizurtrailerapp-env.elasticbeanstalk.com/api/index.php/api/
);
