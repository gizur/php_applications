<?php

//This file contains the commonly used variables 
include 'modules/CikabTroubleTicket/dynamodb.config.php';

$_cache = array();

$nFact = new NoSQLFactory();
$nIns = $nFact->getInstance();

$toGet = array('id', 'parent_tab_info_array', 'parent_child_tab_rel_array');

$result = $nIns->get_item($parent_tabdata_table_name, $toGet, 'id', $gizur_client_id);

if ($result) {    
    $_cache['id'] = $gizur_client_id;
    $_cache['parent_tab_info_array'] = (String) $result['parent_tab_info_array'];
    $_cache['parent_child_tab_rel_array'] = (String) $result['parent_child_tab_rel_array'];
} else {
    $_cache = create_parenttab_data_file();
}

if (isset($_cache) && !empty($_cache)) {
    eval("\$parent_tab_info_array=" . $_cache['parent_tab_info_array'] . ";");
    eval("\$parent_child_tab_rel_array=" . $_cache['parent_child_tab_rel_array'] . ";");
}
?>
