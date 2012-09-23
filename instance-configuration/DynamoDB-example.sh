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
$_GET['email'] = clab@gizur.com;

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
        $result->{$key} = 
                  (string)$item->{AmazonDynamoDB::TYPE_STRING};
    }

    $response->success = true;
    $response->result = $result;

	//$this->_sendResponse(200, json_encode($response));
	// printing, just for testing purposes
	print $response;

} else {
    $response->success = false;
    $response->error->code = "NOT_FOUND";
	$response->error->message = $_GET['email'] . " was " . " not found";

	//$this->_sendResponse(404, json_encode($response));      
	// printing, just for testing purposes
	print $response;
}


