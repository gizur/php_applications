<?php

//This file contains the commonly used variables 

include 'modules/CikabTroubleTicket/dynamodb.config.php';

$_cache = array();

$nFact = new NoSQLFactory();
$nIns = $nFact->getInstance();

$toGet = array('id', 'tab_info_array', 'tab_seq_array', 'tab_ownedby_array', 'action_id_array', 'action_name_array');
$result = $nIns->get_item($tabdata_table_name, $toGet, 'id', $gizur_client_id);
      
if (!empty($result)) {
    $_cache['id'] = $gizur_client_id;
    $_cache['tab_info_array'] = $result['tab_info_array'];
    $_cache['tab_seq_array'] = $result['tab_seq_array'];
    $_cache['tab_ownedby_array'] = $result['tab_ownedby_array'];
    $_cache['action_id_array'] = $result['action_id_array'];
    $_cache['action_name_array'] = $result['action_name_array'];
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

require_once 'auto_privilege.php';
?>
