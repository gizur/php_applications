<?php
/**   
     * @category   Cronjobs
	 * @package    Integration
	 * @subpackage DatabaseConfig
	 * @author     Anil Singh <anil-singh@essindia.co.in>
	 * @version    SVN: $Id$
	 * @link       href="http://gizur.com"
	 * @license    Commercial license
	 * @copyright  Copyright (c) 2012, Gizur AB, <a href="http://gizur.com">Gizur Consulting</a>, All rights reserved.
	 *
	 * purpose : connect to mysql server
	* Coding standards:
	* http://pear.php.net/manual/en/standards.php
	*
	* PHP version 5.3
	*
	*/ 
?>
<?php
 /* 
  * For Use Rabbit MQ Connection Auth 
  */
/**
 * Below file Global Liberaly file
 */
 require_once 'config.inc.php';
 /**
  * User RabbitMQ autoload
 */
 require_once 'autoload.php';
 
 use PhpAmqpLib\Connection\AMQPConnection;
/**
 *ready state of syslog
 */
openlog("RabbitMQConnectionCron", LOG_PID | LOG_PERROR, LOG_LOCAL0);
?>
<?php
				/**
				 Call AMQP Connection     
				*/
			 $conn = new AMQPConnection($rbconfig_rabbitmq['rmq_server'], 
                                                    $rbconfig_rabbitmq['rmq_port'], 
                                                    $rbconfig_rabbitmq['rmq_username'], 
                                                    $rbconfig_rabbitmq['rmq_password'], 
                                                    $rbconfig_rabbitmq['virtual_host']);
			 if ($conn) {
			 $ch = $conn->channel();
			/**
			 * Check RabbitMQ Channel Connection if any issue in RabbitMQ Channel then manage error in syslog
			*/ 
			 if(!$ch){
			  $syslogmessage="Some problem in Rabbitmq Channel Connection.";
			  syslog(LOG_WARNING, "".$syslogmessage."");	
			  exit;
			  }	
			 }						  
		    else{	 
			/**
			* Check RabbitMQ Connection if any issue in RabbitMQ then manage error in syslog
			*/ 
			if(!$conn){
			$syslogmessage="Some problem in Rabbitmq Connection.please check username and password!";
			 syslog(LOG_WARNING, "".$syslogmessage."");	
			exit;
		}	

					
    }		
?>

