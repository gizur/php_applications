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
                    'class'=>'CDummyCache',
                    'class'=>'CMemCache',
                    'servers'=>array(
                        array(
                            'host'=>'10.58.226.192',//'localhost',//gizurcloud-1c.i4vamf.0001.euw1.cache.amazonaws.com',
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
                        array('api/list', 'pattern'=>'/<model:(HelpDesk|Assets|About)>', 'verb'=>'GET'),
                        array('api/list', 
                                   'pattern'=>'/<model:(HelpDesk)>/<category:(inoperation|damaged|all)>', 
                                   'verb'=>'GET'),
                        array('api/list', 
                                   'pattern'=>'/<model:(HelpDesk)>/<category:(inoperation|damaged|all)>/<year:\d{4}>/<month:\d{2}>/<trailerid:\w+>/<reportdamage:(yes|no|all)>', 
                                   'verb'=>'GET'),
                        array('api/view', 'pattern'=>'/<model:(HelpDesk|Assets|DocumentAttachments)>/<id:[0-9x]+>', 'verb'=>'GET'),
                        array('api/view', 'pattern'=>'/<model:(User)>/<email:.+>', 'verb'=>'GET'),
                        array('api/list', 'pattern'=>'/<model:(HelpDesk|Assets)>/<fieldname:\w+>', 'verb'=>'GET'), 
                        array('api/list', 'pattern'=>'/<model:(Authenticate)>/<action:(login|logout)>', 'verb'=>'POST'),
                        array('api/update', 'pattern'=>'/<model:(Authenticate)>/<action:(reset|changepw)>', 'verb'=>'PUT'),
                        array('api/update', 'pattern'=>'/<model:(HelpDesk|Assets)>/<id:[0-9x]+>', 'verb'=>'PUT'),
			            array('api/update', 'pattern'=>'/<model:(User)>/', 'verb'=>'PUT'),
                        array('api/update', 'pattern'=>'/<model:(User)>/<field:(keypair1|keypair2)>/<email:.+>', 'verb'=>'PUT'),
                        array('api/create', 'pattern'=>'/<model:(HelpDesk|User)>', 'verb'=>'POST'),
                        array('api/update', 'pattern'=>'/<model:(Cron)>/<action:(mailscan)>', 'verb'=>'PUT'),
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
					'levels'=>'trace, error, warning',
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
                'vtRestUrl'   => 'http://phpapplications-env-sixmtjkbzs.elasticbeanstalk.com/clab/vtiger/webservice.php',
                'vtCronPath'   => '/var/www/html/lib/vtiger-5.4.0/cron/',             
                'awsS3Bucket' => 'gizurcloud',
                'awsDynamoDBTableName' => 'GIZUR_ACCOUNTS',
                'awsSESFromEmailAddress' => 'noreply@gizur.com',
                'awsSESClientEmailAddress' => 'gizur-ess-anshuk@gizur.com',
                'acceptableTimestampError' => 60,
                'awsS3Region' => 'REGION_APAC_NE1',
                'awsDynamoDBRegion' => 'REGION_EU_W1',
                'awsSESRegion' => 'REGION_EU_W1',
                'custom_fields' => Array(
                    'HelpDesk' => Array(
                        'tickettype' => 'cf_640',
                        'trailerid' => 'cf_641',
                        'damagereportlocation' => 'cf_653',
                        'sealed' => 'cf_643',
                        'plates' => 'cf_652',
                        'straps'  => 'cf_651',
                        'reportdamage' => 'cf_654',
                        'damagetype' => 'cf_647',
                        'damageposition' => 'cf_648',
                        'drivercauseddamage' => 'cf_649'
                    ),
                    'Assets' => Array(
                        'trailertype' => 'cf_650'
                    )
                ),  
	),
);
