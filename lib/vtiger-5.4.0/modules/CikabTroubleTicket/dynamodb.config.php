<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
require_once '../aws-php-sdk/sdk.class.php';
global $gizur_client_id;

$tabdata_table_name = 'VTIGER_TABDATA';
$parent_tabdata_table_name = 'VTIGER_PARENT_TABDATA';
$table_region = AmazonDynamoDB::REGION_EU_W1;

$dynamodb = new AmazonDynamoDB();
$dynamodb->set_region($table_region);
?>
