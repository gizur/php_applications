<?php
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);
require_once '/var/www/html/lib/aws-php-sdk/sdk.class.php';
global $gizur_client_id, $_is_active_dynamodb;

$tabdata_table_name = 'VTIGER_TABDATA';
$parent_tabdata_table_name = 'VTIGER_PARENT_TABDATA';
$dynamodb_table_region = "AmazonDynamoDB::REGION_EU_W1";
?>
