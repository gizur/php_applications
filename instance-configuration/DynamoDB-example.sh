#!/usr/bin/php

<?php
/**
 * Yii Controller to handel REST queries
 *
 * Works with remote vtiger REST service
 *
 * @package        	GizurCloud
 * @subpackage    	Instance-configuration
 * @category    	Shell Script
 * @author        	Jonas Colmsjö
 **/

/*
 * Including Amazon classes
 */



require_once('../lib/aws-php-sdk/sdk.class.php');


// JUST TESTING
$_GET['email'] = 'clab@gizur.com';

// Instantiate the class
$dynamodb = new AmazonDynamoDB();
$dynamodb->set_region(AmazonDynamoDB::REGION_EU_W1); 
$table_name = 'GIZUR_ACCOUNTS';

// Get an item
$ddb_response = $dynamodb->get_item(array(
    'TableName' => $table_name,
    'Key' => $dynamodb->attributes(array('HashKeyElement'  => $_GET['email'], )),
    'ConsistentRead' => 'true'
));
        
if (isset($ddb_response->body->Item)) {
    foreach($ddb_response->body->Item->children() as $key => $item) {
        $result->{$key} = (string)$item->{AmazonDynamoDB::TYPE_STRING};
    }

    $response->success = true;
    $response->result = $result;

	//$this->_sendResponse(200, json_encode($response));
	// printing, just for testing purposes
	print json_encode($response);

} else {
    $response->success = false;
    $response->error->code = "NOT_FOUND";
	$response->error->message = $_GET['email'] . " was " . " not found";

	//$this->_sendResponse(404, json_encode($response));      
	// printing, just for testing purposes
	print json_encode($response);
}



include("gc1-ireland/rest-api/config.inc.php");
require_once 'MDB2.php';

/**
 *  The Pear PHP dagtabase API MDB2 will is used
 *  http://pear.php.net/manual/en/package.database.mdb2.php
 */


/**
 * Database connection string
 * @global string $dsn
 *
 * Example 'mysql://root:mysecret@localhost/mysql'
 */
$dsn = "mysql://" . $dbconfig['db_username'] . ":" . $dbconfig['db_password'] . "@" . $dbconfig['db_server'] . $dbconfig['db_port'] . "/" . $dbconfig['db_name'];


/**
 * Database connection options
 * @global string $options
 */
$options = array(
    'persistent' => true,
);

/**
 * Database MDB2 connection object 
 * @global mixed $mdb2
 */
$mdb2 =& MDB2::factory($dsn, $options);

if (PEAR::isError($mdb2)) {
    echo ($mdb2->getMessage().' - '.$mdb2->getUserinfo());
}



/**
 * Create database and user 
 *
 * @param mixed $mdb2
 * @param mixed $username
 * @param mixed $password
 * @return int
 */
function createUser($mdb2, $username, $password) {

   /**
    * Create database
    *
    * Example SQL from myPhpAdmin:
    *
    *  CREATE USER 'test3'@'%' IDENTIFIED BY '***';
    *  GRANT USAGE ON *.* TO 'test3'@'%' IDENTIFIED BY '***' 
    *  WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
    *  
    *  CREATE DATABASE IF NOT EXISTS `test3`;
    *  GRANT ALL PRIVILEGES ON `test3`.* TO 'test3'@'%';
    */

    $query = <<<EOT
        CREATE USER '$username'@'%' IDENTIFIED BY '$password';
        GRANT USAGE ON *.* TO '$username'@'%' IDENTIFIED BY '$password' 
        WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
EOT;

     
//        CREATE DATABASE IF NOT EXISTS `$username`;
//        GRANT ALL PRIVILEGES ON `$username`.* TO '$username'@'%';
//EOT; 

    // Execute the query
    $result = $mdb2->exec($query);

    // check if the query was executed properly
    if (PEAR::isError($result)) {
        echo ($result->getMessage().' - '.$result->getUserinfo());
        exit();
    }
    

    // Disconnect from the database
    $mdb2->disconnect();

    return 0;
}

// create user
$result = createUser($mdb2, 'test2', 'test1');
print "User created successfully!\n";

// create table
// $result = createTable($mdb2);
//print "Table created successfully!\n";

?>

