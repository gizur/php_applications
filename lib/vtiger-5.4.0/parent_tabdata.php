<?php

//This file contains the commonly used variables 
include 'modules/CikabTroubleTicket/dynamodb.config.php';
    
$dynamodb = new AmazonDynamoDB();
$dynamodb->set_region(constant($dynamodb_table_region));
// Get an item
$response = $dynamodb->get_item(
    array(
        'TableName' => $parent_tabdata_table_name,
        'Key' => $dynamodb->attributes(array('HashKeyElement' => $gizur_client_id)),
        'ConsistentRead' => 'true'
    )
);

if (isset($response->body->Item)) {
    $items = $response->body->Item;
    eval("\$parent_tab_info_array=" . $items->parent_tab_info_array->{AmazonDynamoDB::TYPE_STRING} . ";");
    eval("\$parent_child_tab_rel_array=" . $items->parent_child_tab_rel_array->{AmazonDynamoDB::TYPE_STRING} . ";");
} else {
    create_parenttab_data_file();
}
?>