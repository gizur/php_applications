<?php

require_once '../lib/aws-php-sdk/sdk.class.php';
$gizur_client_id = 'cikab';
$dynamodb = new AmazonDynamoDB();
$region = 'REGION_EU_W1';
$table_name = 'VTIGER_TABDATA';
$dynamodb->set_region("AmazonDynamoDB::" . $region);


// Prepare the data
$post['id'] = $gizur_client_id;
$post['tab_info_array'] = 'constructArray($result_array)';
$post['tab_seq_array'] = 'constructArray($seq_array)';
$post['tab_ownedby_array'] = 'constructArray($ownedby_array)';
$post['action_id_array'] = 'constructSingleStringKeyAndValueArray($actionid_array)';
$post['action_name_array'] = 'constructSingleStringValueArray($actionname_array)';
echo "In create_tab_data_file() $gizur_client_id";

$ddb_response = $dynamodb->put_item(
    array(
        'TableName' => $table_name,
        'Item' => $dynamodb->attributes($post)
    )
);

print_r($ddb_response);
?>
