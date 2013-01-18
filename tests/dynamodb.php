<?php

require_once '../lib/aws-php-sdk/sdk.class.php';
$gizur_client_id = 'cikab';
$dynamodb = new AmazonDynamoDB();
$region = 'REGION_EU_W1';
$table_name = 'VTIGER_TABDATA';
$dynamodb->set_region(AmazonDynamoDB::REGION_EU_W1);

$response = $dynamodb->get_item(
    array(
        'TableName' => $table_name,
        'Key' => $dynamodb->attributes(array('HashKeyElement' => $gizur_client_id)),
        'ConsistentRead' => 'true'
    )
);
print_r($response);
/*
  $queue = new CFBatchRequest();
  $queue->use_credentials($dynamodb->credentials);
  // Prepare the data
  $post['id'] = array(AmazonDynamoDB::TYPE_STRING => $gizur_client_id);
  $post['tab_info_array'] = array(AmazonDynamoDB::TYPE_STRING => 'constructArray($result_array)');
  $post['tab_seq_array'] = array(AmazonDynamoDB::TYPE_STRING => 'constructArray($seq_array)');
  $post['tab_ownedby_array'] = array(AmazonDynamoDB::TYPE_STRING => 'constructArray($ownedby_array)');
  $post['action_id_array'] = array(AmazonDynamoDB::TYPE_STRING => 'constructSingleStringKeyAndValueArray($actionid_array)');
  $post['action_name_array'] = array(AmazonDynamoDB::TYPE_STRING => 'constructSingleStringValueArray($actionname_array)');
  echo "In create_tab_data_file() $gizur_client_id";

  $dynamodb->batch($queue)->put_item(
  array(
  'TableName' => $table_name,
  'Item' => $post
  )
  );

  $responses = $dynamodb->batch($queue)->send();
 */
if ($responses->areOK()) {
    echo "The data has been added to the table." . PHP_EOL;
} else {
    print_r($response);
}
?>