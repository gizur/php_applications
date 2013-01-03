<?php
/**   
     * @category   Cronjobs
     * 
	 * @package    Integration
	 * @subpackage DatabaseConfig
	 * @author     Anil Singh <anil-singh@essindia.co.in>
	 * @link       href="http://gizur.com"
	 * @copyright  Copyright (c) 2012,Gizur AB, <a href="http://gizur.com">Gizur Consulting</a>.
	 * @license    Commercial license
	 * @version    SVN: $Id$
	 * purpose : connect to mysql server
	* Coding standards:
	* http://pear.php.net/manual/en/standards.php
	* PHP version 5.3.2
    */ 
    
	/**
	 * 
	 * Call Configration Files
	 * 
	 */ 	
 require_once __DIR__.'/config.inc.php' ;
?>
<?php
/**
 * 
 *ready state of syslog
 * 
 */
openlog ("DatabaseConnetionCron", LOG_PID | LOG_PERROR, LOG_LOCAL0) ;

/**
 * 
 * Database Connection Class
 * 
 */ 
class Connect
  {
		private  $host;
	    private  $user;
        private  $pass;
        private  $data;
        public   $link;
        public  $interfacedata;
      /**
		 * 
		 * construct function use this function auto load
		 * 
		 */  
 	 public function __construct ($host,$user,$pass,$interfacedata)
 	  {
	  	$this->host=$host;
		$this->username=$user;
		$this->pass=$pass;
		$this->database=$interfacedata;
		$this->mysqlconnection();
	 }
	 /**
	     * 
		 * destruct function use this finction auto close connection 
		 * 
		 */ 
	 public function __destruct()
	 {
		 mysql_close($this->link);
	 }
	   /**
	     *  
		 * Main function use this finction connect to the mysql server
		 *   
		 */ 
	 private function mysqlconnection()
	 {
				$this->link=mysql_connect($this->host,$this->username,$this->pass,true) or die(mysql_error());
				if ($this->link) {
				 $this->db_selected = mysql_select_db($this->database, $this->link);
				}
				else {
				 /**
				  * 
				  * Check Database Connection if any issue in connection then manage error in syslog.!
				  * 
				*/  
				if (!$this->link) {
				$syslogmessage="Some problem in ".$this->database." database Connection.please check username and password.!";
				syslog(LOG_WARNING, "".$syslogmessage."");
				exit;
				 }	
			  } 
		  }
 }
 /**
  * 
  * Create database connection object & call as globaly
  * 
  */ 
$obj1 = new Connect($dbconfig_integration['db_server'],$dbconfig_integration['db_username'],$dbconfig_integration['db_password'],$dbconfig_integration['db_name']);
/**
 * 
 * Master table(vTiger) Table Object
 * 
 */ 
$obj2 = new Connect($dbconfig_vtiger['db_server'],$dbconfig_vtiger['db_username'],$dbconfig_vtiger['db_password'],$dbconfig_vtiger['db_name']);
?>

