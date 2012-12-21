<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Gizur REST Service',
        'defaultController'=>'api',
	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
                'application.extensions.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Enter Your Password Here',
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
                'cache'=>array(
                    'class'=>'CMemCache',
                    'servers'=>array(
                        array(
                            'host'=>'localhost',//'memcached.int.gizur.com',
                            'port'=>11211,
                            'weight'=>100,
                        ),
                    ),
                ),            
		// uncomment the following to enable URLs in path-format
		'urlManager'=>array(
                    'urlFormat'=>'path',
                    'rules'=>array(
                        // REST patterns
                        array('api/list', 'pattern'=>'api/<model:(HelpDesk|Assets|About)>', 'verb'=>'GET'),
                        array('api/list', 
                                   'pattern'=>'api/<model:(HelpDesk)>/<category:(inoperation|damaged|all)>', 
                                   'verb'=>'GET'),
                        array('api/list', 
                                   'pattern'=>'api/<model:(HelpDesk)>/<category:(inoperation|damaged|all)>/<year:\d{4}>/<month:\d{2}>/<trailerid:\w+>/<reportdamage:(yes|no|all)>', 
                                   'verb'=>'GET'),
                        array('api/view', 'pattern'=>'api/<model:(HelpDesk|Assets|DocumentAttachments)>/<id:[0-9x]+>', 'verb'=>'GET'),
                        array('api/view', 'pattern'=>'api/<model:(User)>/<email:.+>', 'verb'=>'GET'),
                        array('api/list', 'pattern'=>'api/<model:(HelpDesk|Assets)>/<fieldname:\w+>', 'verb'=>'GET'), 
                        array('api/list', 'pattern'=>'api/<model:(Authenticate)>/<action:(login|logout)>', 'verb'=>'POST'),
                        array('api/update', 'pattern'=>'api/<model:(Authenticate)>/<action:(reset|changepw)>', 'verb'=>'PUT'),
                        array('api/update', 'pattern'=>'api/<model:(HelpDesk|Assets)>/<id:[0-9x]+>', 'verb'=>'PUT'),
			            array('api/update', 'pattern'=>'api/<model:(User)>/', 'verb'=>'PUT'),
                        array('api/update', 'pattern'=>'api/<model:(User)>/<field:(keypair1|keypair2)>/<email:.+>', 'verb'=>'PUT'),
                        array('api/create', 'pattern'=>'api/<model:(HelpDesk|User)>', 'verb'=>'POST'),
                        array('api/update', 'pattern'=>'api/<model:(Cron)>/<action:(mailscan)>', 'verb'=>'PUT'),
                        array('api/error', 'pattern'=>'.*?')
                    ),
		),
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
					'levels'=>'error, warning, trace, info',
				),
				array(
					'class'=>'CLiveLogRoute',
					'levels'=>'error, warning, trace',
                    'server'=>'http://gizur.herokuapp.com/log'
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
                'vtRestUrl'   => 'https://api.gizur.com/lib/vtiger-5.4.0/webservice.php',
                'vtCronPath'   => '/var/www/html/lib/vtiger-5.4.0/cron/',            
                'awsS3Bucket' => 'gizurcloud-clab',
                'awsDynamoDBTableName' => 'GIZUR_ACCOUNTS',
                'awsSESFromEmailAddress' => 'noreply@gizur.com',
                'awsSESClientEmailAddress' => 'admin@gizur.com',
                'acceptableTimestampError' => 60,
                'awsS3Region' => 'REGION_EU_W1',
                'awsDynamoDBRegion' => 'REGION_EU_W1',
                'awsSESRegion' => 'REGION_EU_W1',
                'custom_fields' => Array(
                    'HelpDesk' => Array(
                        'tickettype' => 'cf_649',
                        'trailerid' => 'cf_640',
                        'damagereportlocation' => 'cf_661',
                        'sealed' => 'cf_651',
                        'plates' => 'cf_662',
                        'straps'  => 'cf_663',
                        'reportdamage' => 'cf_654',
                        'damagetype' => 'cf_659',
                        'damageposition' => 'cf_658',
                        'drivercauseddamage' => 'cf_657'
                    ),
                    'Assets' => Array(
                        'trailertype' => 'cf_660'
                    )
                ),  
	),
);
