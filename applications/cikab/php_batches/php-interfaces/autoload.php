<?php
/* * *************************  RABBITMQ CONFIGURATION ************************ */

/**
 * the RabbitMQ server host when installed. 
 * @global  string $dbconfig_integration['rmq_server'];
 */
$rbconfig_rabbitmq['rmq_server'] = 'mq.gizurcloud.com';

/**
 * The Rabbit MQ Server Port No. default is 5672
 * @global string $dbconfig_integration['rmq_port']
 */
$rbconfig_rabbitmq['rmq_port'] = '5672';

/**
 * The Rabbit MQ Server user name when loggin into the RMQ Server.
 */
$rbconfig_rabbitmq['rmq_username'] = 'mq-test';

/**
 *  The Rabbit MQ password when login into the RMQ server
 * 
 */
$rbconfig_rabbitmq['rmq_password'] = 'crab9pRe7uwruyuj';


/**
 *  The Rabbit MQ password when login into the RMQ server
 *
 */
$rbconfig_rabbitmq['virtual_host'] = 'test';


/**
 *
 * TEMPORARY, IN ORDER TO GET ON WITH TESTING
 */
include_once(__DIR__.'/autoload.php');


/*
  define('HOST', 'mq.gizurcloud.com');
  define('PORT', 5672);
  define('USER', 'mq-test');
  define('PASS', 'crab9pRe7uwruyuj');
  define('VHOST', 'test');
 */

//If this is enabled you can see AMQP output on the CLI
define('AMQP_DEBUG', false);

require_once('/opt/lib/php-amqplib/vendor/symfony/Symfony/Component/ClassLoader/UniversalClassLoader.php');

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
            'PhpAmqpLib' => '/opt/lib/php-amqplib',
        ));

$loader->register();
