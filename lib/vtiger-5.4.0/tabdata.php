<?php

//This file contains the commonly used variables 

include 'modules/CikabTroubleTicket/dynamodb.config.php';
global $memcache_url;
$_cache = array();
$memcache = new Memcache;
if ($memcache->connect($memcache_url, 11211)) {
    $_tabdata_cache = $memcache->get($gizur_client_id . "_tabdata_details");
    $_cache = $_tabdata_cache;
} else {
    unset($memcache);
    $_tabdata_cache = false;
}

if (!$_tabdata_cache && true) {
    $dynamodb = new AmazonDynamoDB();
    $dynamodb->set_region(constant($dynamodb_table_region));
    // Get an item
    $response = $dynamodb->get_item(
        array(
            'TableName' => $tabdata_table_name,
            'Key' => $dynamodb->attributes(array('HashKeyElement' => $gizur_client_id)),
            'ConsistentRead' => 'true'
        )
    );

    if (isset($response->body->Item)) {
        $_items = $response->body->Item;

        $_cache['id'] = $gizur_client_id;
        $_cache['tab_info_array'] = $_items->tab_info_array->{AmazonDynamoDB::TYPE_STRING};
        $_cache['tab_seq_array'] = $_items->tab_seq_array->{AmazonDynamoDB::TYPE_STRING};
        $_cache['tab_ownedby_array'] = $_items->tab_ownedby_array->{AmazonDynamoDB::TYPE_STRING};
        $_cache['action_id_array'] = $_items->action_id_array->{AmazonDynamoDB::TYPE_STRING};
        $_cache['action_name_array'] = $_items->action_name_array->{AmazonDynamoDB::TYPE_STRING};
        if (isset($memcache)) {
            $memcache->set($gizur_client_id . "_tabdata_details", $_cache);
        }
    } else {
        $_cache = create_tab_data_file();
    }
} else {
    $_cache = create_tab_data_file();
}

if (isset($_cache) && !empty($_cache)) {
    eval("\$tab_info_array=" . $_cache['tab_info_array'] . ";");
    eval("\$tab_seq_array=" . $_cache['tab_seq_array'] . ";");
    eval("\$tab_ownedby_array=" . $_cache['tab_ownedby_array'] . ";");
    eval("\$action_id_array=" . $_cache['action_id_array'] . ";");
    eval("\$action_name_array=" . $_cache['action_name_array'] . ";");
}
?>
