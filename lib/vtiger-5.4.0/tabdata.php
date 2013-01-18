<?php

//This file contains the commonly used variables 

require_once 'modules/CikabTroubleTicket/dynamodb.config.php';

$dynamodb = new AmazonDynamoDB();
$dynamodb->set_region(constant($table_region));
// Get an item
$response = $dynamodb->get_item(
    array(
        'TableName' => $tabdata_table_name,
        'Key' => $dynamodb->attributes(array('HashKeyElement' => $gizur_client_id)),
        'ConsistentRead' => 'true'
    )
);

if (isset($response->body->Item)) {
    $items = $response->body->Item;
    eval("\$tab_info_array=". $items->tab_info_array->{AmazonDynamoDB::TYPE_STRING}.";");
    eval("\$tab_seq_array=". $items->tab_seq_array->{AmazonDynamoDB::TYPE_STRING}.";");
    eval("\$tab_ownedby_array=". $items->tab_ownedby_array->{AmazonDynamoDB::TYPE_STRING}.";");
    eval("\$action_id_array=". $items->action_id_array->{AmazonDynamoDB::TYPE_STRING} .";");
    eval("\$action_name_array=". $items->action_name_array->{AmazonDynamoDB::TYPE_STRING} .";");
} else {
    create_tab_data_file();
}
?>