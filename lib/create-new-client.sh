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
 *
 *
 * parse_str(implode('&', array_slice($argv, 1)), $_GET);
 * $ php -f somefile.php a=1 b[]=2 b[]=3
 * This will set $_GET['a'] to '1' and $_GET['b'] to array('2', '3').
 *
 **/


include("../api/protected/config/config.inc.php");
require_once 'MDB2.php';

// The script for creating tables
include("vtiger-5.4.0-database.sql.php");

/*
 * Including Amazon classes
 */

require_once('aws-php-sdk/sdk.class.php');


/*
 * Global variables for MySQL credentials  
 *
 */

$db_server     = '';
$db_port       = '';
$db_username   = '';
$db_password   = '';
$db_name       = '';


/**
 * Execute SQL Statement 
 *
 * @param mixed $mdb2
 * @param string $stmt
 */
function execSQLStatement($mdb2, $stmt) {

    // Execute the query
    $result = $mdb2->exec($stmt);

    // check if the query was executed properly
    if (PEAR::isError($result)) {
        echo ($result->getMessage().' - '.$result->getUserinfo());
        exit();
    }

    return 0;
}

/**
 * Create database and user 
 *
 * @param mixed $mdb2
 * @param mixed $username
 * @param mixed $password
 * @return int
 */
function createUser($mdb2) {

   global $db_username, $db_password, $db_name;

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


    /*
     * CREATE USER AND GRANT USAGE
     */


    /*
     * NOTE:
     *
     * 'CREAT USER IF NOT EXISTS' is not supported by MySQL
     * The GRANT statement below will create the user if it does not exist though!!
     *
     *    $query = <<<EOT
     *   CREATE USER '$username'@'%' IDENTIFIED BY '$password';
     * EOT;
     * execSQLStatement($mdb2, $query);
     */


    $query = <<<EOT
        GRANT USAGE ON *.* TO '$db_username'@'%' IDENTIFIED BY '$db_password' 
        WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
EOT;

    execSQLStatement($mdb2, $query);


    /*
     * CREATE DATABASE
     */

    $query = <<<EOT
        CREATE DATABASE IF NOT EXISTS `$db_name`;
EOT;

    execSQLStatement($mdb2, $query);


    /*
     * GRANT PRIVILEGES TO DATABASE
     */

    $query = <<<EOT
        GRANT ALL PRIVILEGES ON `$db_username`.* TO '$db_name'@'%';
EOT;

    execSQLStatement($mdb2, $query);

    return 0;
}

/**
 * Import tables into database
 *
 * @param mixed $mdb2
 * @return int
 */
function importTables($mdb2) {

    global $create_tables_query;

    execSQLStatement($mdb2, $create_tables_query);

    return 0;
}


/*
 * Parse arguments
 */
parse_str(implode('&', array_slice($argv, 1)), $_GET);

if( ! isset($_GET['email']) ) {
    print "USAGE:";
    print "./create-new-client email=name@exampole.com\n";
    exit();
}


/*
 * Fetch credentials for the user to create from the AmazonDynamoDB
 */

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

    $db_server     = $result->{'server'};
    $db_port       = $result->{'port'};;
    $db_username   = $result->{'username'};;
    $db_password   = $result->{'dbpassword'};;
    $db_name       = $result->{'databasename'};;


    // TODO: make REST service out of this script
	//$this->_sendResponse(200, json_encode($response));

} else {
    $response->success = false;
    $response->error->code = "NOT_FOUND";
	$response->error->message = $_GET['email'] . " was " . " not found";

    // TODO: make REST service out of this script
	//$this->_sendResponse(404, json_encode($response));      
	print json_encode($response) . "\n";

    exit();
}


/* ---------------------------------------------------------------
 * 
 * Create user and database
 *
 */



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
$dsn = "mysql://" . $dbconfig['db_username'] . ":" . $dbconfig['db_password'] . "@" . $db_server . ":" . $db_port . "/" . $dbconfig['db_name'];


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
    exit();
}


$result = createUser($mdb2);
print "\nMySQL User: $db_username ($db_password ) and database:$db_name created successfully on $db_server:$db_port !\n";

// Disconnect from the database
$mdb2->disconnect();


/* ---------------------------------------------------------------
 * 
 * Import tabels into the new database
 *
 */

/*
 * Connect with the user just created
 *
 */
$dsn = "mysql://" . $db_username  . ":" . $db_password . "@" . $db_server . ":" . $db_port . "/" . $db_name;


/**
 * Database MDB2 connection object 
 * @global mixed $mdb2
 */
$mdb2 =& MDB2::factory($dsn, $options);

if (PEAR::isError($mdb2)) {
    echo ($mdb2->getMessage().' - '.$mdb2->getUserinfo());
    exit();
}

$result = importTables($mdb2);
print "\nImport tables into  database:$db_name on $db_server:$db_port !\n";


// Disconnect from the database
$mdb2->disconnect();

?>

