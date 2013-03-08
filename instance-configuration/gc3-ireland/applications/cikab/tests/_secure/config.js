// config.js
//------------------------------
//
// 2013-03-05, Prabhat Khera
//
// Copyright Gizur AB 2012
//
// File with config
//
// Documentation is 'docco style' - http://jashkenas.github.com/docco/
//
// Using Google JavaScript Style Guide - 
// http://google-styleguide.googlecode.com/svn/trunk/javascriptguide.xml
//
//------------------------------


(function(){

    // Global Config object
    // with browser window or with node module
    var Config = (typeof window === 'undefined') ? exports.Config = {} : window.Config = {}; 


    // Globals with configuration
    //===============================================

    // These should be moved to environment variables
    //-----------------------------------------------

    // Host Configuration used to call php cron job
    var HOSTNAME = Config.HOSTNAME = 'phpapplications-env-sixmtjkbzs.elasticbeanstalk.com';
    var IS_HTTPS = Config.IS_HTTPS = false;
    var SERVER_PORT = Config.SERVER_PORT = 80;
    
    // PHP CRON JOB PATHS
    var PHP_BATCHES_1 = Config.PHP_BATCHES_1 = '/applications/cikab/php_batches/php-interfaces/sales_orders/testbatches.php?action=phpcronjob1';
    var PHP_BATCHES_2 = Config.PHP_BATCHES_2 = '/applications/cikab/php_batches/php-interfaces/sales_orders/testbatches.php?action=phpcronjob2';
    var PHP_BATCHES_3 = Config.PHP_BATCHES_3 = '/applications/cikab/php_batches/php-interfaces/sales_orders/testbatches.php?action=phpcronjob3';

    //vTiger Database configurations
    var DB_HOST = Config.DB_HOST = 'gizurcloud.colm85rhpnd4.eu-west-1.rds.amazonaws.com';
    var DB_NAME = Config.DB_NAME = 'vtiger_7cd70dc3';
    var DB_USER = Config.DB_USER = 'user_6bd70dc3';
    var DB_PASSWORD = Config.DB_PASSWORD = 'fbd70dc30c05';
    var DB_PORT = Config.DB_PORT = '3306';
    
    //Amazon Queue configurations
    var Q_URL = Config.Q_URL = 'https://sqs.eu-west-1.amazonaws.com/065717488322/cikab_queue';
    var AWS_REGION = Config.AWS_REGION = 'eu-west-1';
    
    // Integration Database configurations
    var DB_I_HOST = Config.DB_I_HOST = 'gizurcloud.colm85rhpnd4.eu-west-1.rds.amazonaws.com';
    var DB_I_NAME = Config.DB_I_NAME = 'vtiger_integration';
    var DB_I_USER = Config.DB_I_USER = 'vtiger_integrati';
    var DB_I_PASSWORD = Config.DB_I_PASSWORD = 'ALaXEryCwSFyW5jQ';
    var DB_I_PORT = Config.DB_I_PORT = '3306';

})();
