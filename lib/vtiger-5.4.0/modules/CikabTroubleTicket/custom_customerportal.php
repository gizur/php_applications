<?php
require_once 'modules/SalesOrder/SalesOrder.php';
/* Created Anil Singh */

$server->register(
	'get_list_preorder',
	array('id'=>'xsd:string','block'=>'xsd:string','sessionid'=>'xsd:string','only_mine'=>'xsd:string'),
	array('return'=>'tns:field_datalist_array'),
	$NAMESPACE);
	
$server->register(
	'get_list_cikabsalesorder',
	array('id'=>'xsd:string','block'=>'xsd:string','sessionid'=>'xsd:string','only_mine'=>'xsd:string'),
	array('return'=>'tns:field_datalist_array'),
	$NAMESPACE);
		
$server->register(
	'get_list_cikabVendorPortal',
	array('id'=>'xsd:string','block'=>'xsd:string','sessionid'=>'xsd:string','only_mine'=>'xsd:string'),
	array('return'=>'tns:field_datalist_array'),
	$NAMESPACE);
	
$server->register(
	'create_custom_ticket',
	array('fieldname'=>'tns:common_array'),
	array('return'=>'tns:common_array'),
	$NAMESPACE);

/* End Functions */
    
/* ADDED BY PRABHAT KHERA ON 03 DEC 2012 */
    
$server->register(
    'create_salesorder', 
    array('fieldname' => 'tns:common_array'), 
    array('return' => 'tns:common_array'), 
    $NAMESPACE);

/*  For Create Function by Anil Singh */
/*

function get_list_preorder($id, $module, $sessionid, $only_mine = 'false')
{

    global $adb, $log, $current_user;
    ini_set('display_errors', 'On');
    $log->debug("Entering customer portal function get_list_preorder");
    $log->debug("get_list_preorder($id, $module, $sessionid, $only_mine)");
    $log->debug("require_once start : get_list_preorder");
    
    if(!@require_once("modules/$module/$module.php"))
        $log->debug("Failed to include modules/$module/$module.php");
    
    require_once('include/utils/UserInfoUtil.php');
    $log->debug("require_once end : get_list_preorder");
        
    $check = checkModuleActive($module);
    if ($check == false) {
        return array("#MODULE INACTIVE#");
    }
    $user = new Users();
    $userid = getPortalUserid();
    $current_user = $user->retrieveCurrentUserInfoFromFile($userid);
    $log->debug("END retrieveCurrentUserInfoFromFile");
    $focus = new $module();
    $focus->filterInactiveFields($module);
    foreach ($focus->list_fields as $fieldlabel => $values) {
        foreach ($values as $table => $fieldname) {
            $fields_list[$fieldlabel] = $fieldname;
        }
    }
    $log->debug("END foreach");
    if (!validateSession($id, $sessionid))
        return null;

    $entity_ids_list = array();
    $entity_accno_list = array();
    $show_all = show_all($module);
    if ($only_mine == 'true' || $show_all == 'false') {
        $contactquery = "SELECT accountid FROM vtiger_contactdetails 
            WHERE contactid = ? AND accountid != 0";

        $contactres = $adb->pquery($contactquery, array($id));
        $no_of_cont = $adb->num_rows($contactres);

        $acc_id = $adb->query_result($contactres, 0, 'accountid');
        array_push($entity_ids_list, $acc_id);
    } else {
        $contactquery = "SELECT contactid, accountid acctid,
            (select account_no from vtiger_account v1 
            where v1.accountid = acctid) as accountno,
		     (select accountname from vtiger_account v1 
             where v1.accountid = acctid) as accountname
		      FROM vtiger_contactdetails " .
            " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = 
                vtiger_contactdetails.contactid" .
            " AND vtiger_crmentity.deleted = 0 " .
            " WHERE (accountid = (SELECT accountid FROM 
                vtiger_contactdetails WHERE contactid = ?)  
                            AND accountid != 0) OR contactid = ?";

        $contactres = $adb->pquery($contactquery, array($id, $id));
        $no_of_cont = $adb->num_rows($contactres);
        for ($i = 0; $i < $no_of_cont; $i++) {
            $cont_id = $adb->query_result($contactres, $i, 'contactid');
            $acc_id = $adb->query_result($contactres, $i, 'acctid');
            $acc_no = $adb->query_result($contactres, $i, 'accountno');
            $accname = $adb->query_result($contactres, $i, 'accountname');
            if (!in_array($cont_id, $entity_ids_list))
                $entity_ids_list[] = $cont_id;
            if (!in_array($acc_id, $entity_ids_list) && $acc_id != '0')
                $entity_ids_list[] = $acc_id;
        }
        $log->debug("ACCOUNTID : " . json_encode($entity_ids_list));
    }

    $queryquotesfortroublet = "SELECT DISTINCT i.productid,i.id , i.quantity,
        p.product_no productno ,p.productname,p.productsheet,
        sum(i.quantity) as totalquotes,
        (SELECT SUM(i2.quantity) FROM vtiger_inventoryproductrel i2
        INNER JOIN vtiger_products p1 on p1.productid=i2.productid
        INNER JOIN vtiger_crmentity CE on CE.crmid=i2.id
        WHERE CE.deleted =0 AND i2.id IN(SELECT s2.salesorderid 
        FROM vtiger_salesorder s2 
        WHERE s2.sostatus NOT IN ('Cancelled','Closed') AND
        s2.accountid IN ( " . generateQuestionMarks($entity_ids_list) . " ) 
            AND p1.productid = p.productid )) as totalsales
        FROM vtiger_inventoryproductrel i
        INNER JOIN vtiger_products p on p.productid=i.productid
        INNER JOIN vtiger_crmentity CE2 on CE2.crmid=i.id
        WHERE CE2.deleted = 0 AND i.id IN (SELECT q.quoteid FROM 
        vtiger_quotes q WHERE (q.quotestage NOT 
        IN('Rejected','Delivered','Closed') 
        AND p.discontinued=1 AND  q.accountid IN  
        ( " . generateQuestionMarks($entity_ids_list) . " ) ))
            GROUP BY i.productid";

    //$paramsquotes = array($entity_ids_list,$entity_ids_list);
    $resquotes = $adb->pquery($queryquotesfortroublet, array($entity_ids_list, $entity_ids_list));
    $rowsquotes = $adb->num_rows($resquotes);

    for ($i = 0; $i < $rowsquotes; $i++) {
        $fields_listquotes[$i]['quoteid'] = $adb->query_result($resquotes, $i, 'id');
        $fields_listquotes[$i]['productid'] = $adb->query_result($resquotes, $i, 'productid');
        $fields_listquotes[$i]['totalquotes'] = $adb->query_result($resquotes, $i, 'totalquotes');
        $fields_listquotes[$i]['totalsales'] = $adb->query_result($resquotes, $i, 'totalsales');
        $fields_listquotes[$i]['productno'] = $adb->query_result($resquotes, $i, 'productno');
        $fields_listquotes[$i]['productname'] = $adb->query_result($resquotes, $i, 'productname');
        $fields_listquotes[$i]['productsheet'] = $adb->query_result($resquotes, $i, 'productsheet');
        $fields_listquotes[$i]['accountno'] = $acc_no;
        $fields_listquotes[$i]['accountname'] = $accname;
    }
    $log->debug("Exiting customerportal function get_list_preorder");
    $log->debug("OBJECT : " . json_encode($fields_listquotes));
    return $fields_listquotes;
}*/
function get_list_preorder($id, $module, $sessionid, $only_mine = 'false')
{

    global $adb, $log, $current_user;
    ini_set('display_errors', 'On');
    $log->debug("Entering customer portal function get_list_preorder");
    $log->debug("get_list_preorder($id, $module, $sessionid, $only_mine)");
    $log->debug("require_once start : get_list_preorder");

    if(!@require_once("modules/$module/$module.php"))
        $log->debug("Failed to include modules/$module/$module.php");

    require_once('include/utils/UserInfoUtil.php');
    $log->debug("require_once end : get_list_preorder");

    $check = checkModuleActive($module);
    if ($check == false) {
        return array("#MODULE INACTIVE#");
    }
    $user = new Users();
    $userid = getPortalUserid();
    $current_user = $user->retrieveCurrentUserInfoFromFile($userid);
    $log->debug("END retrieveCurrentUserInfoFromFile");
    $focus = new $module();
    $focus->filterInactiveFields($module);
    foreach ($focus->list_fields as $fieldlabel => $values) {
        foreach ($values as $table => $fieldname) {
            $fields_list[$fieldlabel] = $fieldname;
        }
    }
    $log->debug("END foreach");
    if (!validateSession($id, $sessionid))
        return null;

    $entity_ids_list = array();
    $entity_accno_list = array();
    $show_all = show_all($module);
    if ($only_mine == 'true' || $show_all == 'false') {
        $contactquery = "SELECT accountid FROM vtiger_contactdetails 
            WHERE contactid = ? AND accountid != 0";
 $contactres = $adb->pquery($contactquery, array($id));
        $no_of_cont = $adb->num_rows($contactres);

        $acc_id = $adb->query_result($contactres, 0, 'accountid');
        array_push($entity_ids_list, $acc_id);
    } else {
        $contactquery = "SELECT contactid, accountid acctid,
            (select account_no from vtiger_account v1 
            where v1.accountid = acctid) as accountno,
                     (select accountname from vtiger_account v1 
             where v1.accountid = acctid) as accountname
                      FROM vtiger_contactdetails " .
            " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = 
                vtiger_contactdetails.contactid" .
            " AND vtiger_crmentity.deleted = 0 " .
            " WHERE (accountid = (SELECT accountid FROM 
                vtiger_contactdetails WHERE contactid = ?)  
                            AND accountid != 0) OR contactid = ?";

        $contactres = $adb->pquery($contactquery, array($id, $id));
        $no_of_cont = $adb->num_rows($contactres);
        for ($i = 0; $i < $no_of_cont; $i++) {
            $cont_id = $adb->query_result($contactres, $i, 'contactid');
            $acc_id = $adb->query_result($contactres, $i, 'acctid');
            $acc_no = $adb->query_result($contactres, $i, 'accountno');
            $accname = $adb->query_result($contactres, $i, 'accountname');
            if (!in_array($cont_id, $entity_ids_list))
                $entity_ids_list[] = $cont_id;
            if (!in_array($acc_id, $entity_ids_list) && $acc_id != '0')
                $entity_ids_list[] = $acc_id;
        }
        $log->debug("ACCOUNTID : " . json_encode($entity_ids_list));
    }
    $queryquotesfortroublet = "SELECT DISTINCT i.productid,i.id , i.quantity,
        p.product_no productno ,p.productname,p.productsheet,
        sum(i.quantity) as totalquotes
        FROM vtiger_inventoryproductrel i
        INNER JOIN vtiger_products p on p.productid=i.productid
        INNER JOIN vtiger_crmentity CE2 on CE2.crmid=i.id
 WHERE CE2.deleted = 0 AND i.id IN (SELECT q.quoteid FROM 
        vtiger_quotes q WHERE (q.quotestage NOT 
        IN('Rejected','Delivered','Closed') 
        AND p.discontinued=1 AND  q.accountid IN  
        ( " . generateQuestionMarks($entity_ids_list) . " ) ))
            GROUP BY i.productid";

    //$paramsquotes = array($entity_ids_list,$entity_ids_list);
    $resquotes = $adb->pquery($queryquotesfortroublet, array($entity_ids_list));
    $rowsquotes = $adb->num_rows($resquotes);

    for ($i = 0; $i < $rowsquotes; $i++) {
        $productid=$adb->query_result($resquotes, $i, 'productid');

        $querysalesorder="SELECT SUM(i2.quantity) as totalsales FROM vtiger_inventoryproductrel i2
        left JOIN vtiger_products p1 on p1.productid=i2.productid
        left JOIN vtiger_crmentity CE on CE.crmid=i2.id
        WHERE CE.deleted =0 AND i2.id IN(SELECT salesorderid FROM vtiger_salesorder WHERE sostatus NOT IN ('Cancelled','Closed') AND accountid IN ( " . generateQuestionMarks($entity_ids_list) . " ) and p1.productid=?)";
      $ressalesorder=$adb->pquery($querysalesorder,array($entity_ids_list,$productid));
     $total= $adb->query_result($ressalesorder,0,"totalsales");

        $fields_listquotes[$i]['quoteid'] = $adb->query_result($resquotes, $i, 'id');
        $fields_listquotes[$i]['productid'] = $adb->query_result($resquotes, $i, 'productid');
        $fields_listquotes[$i]['totalquotes'] = $adb->query_result($resquotes, $i, 'totalquotes');
        $fields_listquotes[$i]['totalsales'] = $total;
        $fields_listquotes[$i]['productno'] = $adb->query_result($resquotes, $i, 'productno');
        $fields_listquotes[$i]['productname'] = $adb->query_result($resquotes, $i, 'productname');
        $fields_listquotes[$i]['productsheet'] = $adb->query_result($resquotes, $i, 'productsheet');
        $fields_listquotes[$i]['accountno'] = $acc_no;
        $fields_listquotes[$i]['accountname'] = $accname;
    }
    $log->debug("Exiting customerportal function get_list_preorder");
    $log->debug("OBJECT : " . json_encode($fields_listquotes));
    return $fields_listquotes;
}



/* Check Centeruser by anil Singh */

function getCenteralUser($portaluserid)
{
    global $adb, $log;
    $log->debug("Entering customer portal function getCenteralUser");
    // Look the value from cache first
    $res = $adb->pquery("SELECT cf_617 FROM vtiger_contactscf WHERE contactid = " . $portaluserid . "", array());
    $norows = $adb->num_rows($res);
    if ($norows > 0) {
        $ceneraluser = $adb->query_result($res, 0, 'cf_617');
    } else {
        $ceneraluser = 0;
    }
    return $ceneraluser;
    $log->debug("Exiting customerportal function getPortalUserid");
}

/*  For Create Function by Anil Singh */

function get_list_cikabsalesorder($id, $module, $sessionid, $only_mine = 'false', $status = "", $ACCID)
{
    require_once('modules/' . $module . '/' . $module . '.php');
    require_once('include/utils/UserInfoUtil.php');
    global $adb, $log, $current_user;
    $log->debug("Entering customer portal function get_list_values");
    $check = checkModuleActive($module);
    if ($check == false) {
        return array("#MODULE INACTIVE#");
    }
    $user = new Users();
    $userid = getPortalUserid();
    $current_user = $user->retrieveCurrentUserInfoFromFile($userid);
    if (!validateSession($id, $sessionid))
        return null;
    
    $Wherecases = array();
    $Wherecases[] = "WHERE CE.deleted = 0 ";
    $centerUseris = getCenteralUser($id);
    if ($centerUseris) {
        $paramsquotes = array();
    } else {
        $Wherecases[] = " sa.contactid=? OR sa.accountid=?";
        $paramsquotes = array($id, $ACCID);
    }
    if (!empty($status)) {
        $Wherecases[] = "sa.sostatus='" . $status . "'";
    }

    $Where = implode(' AND ', $Wherecases);

    $queryquotes = "SELECT DISTINCT i.id,i.productid, i.quantity,p.product_no productno ,p.productname,ac.account_no,ac.accountname,sa.sostatus,p.productsheet FROM vtiger_inventoryproductrel i
					  INNER JOIN vtiger_crmentity CE on CE.crmid=i.id
					  INNER JOIN vtiger_products p on p.productid=i.productid
					  INNER JOIN vtiger_salesorder sa on i.id=sa.salesorderid
					  INNER JOIN vtiger_account ac on sa.accountid=ac.accountid
					 
                      " . $Where . "  ORDER BY ac.account_no ASC";

    $resquotes = $adb->pquery($queryquotes, array($paramsquotes));
    $rowsquotes = $adb->num_rows($resquotes);
    for ($i = 0; $i < $rowsquotes; $i++) {
        $fields_listquotes[$i]['quoteid'] = $adb->query_result($resquotes, $i, 'id');
        $fields_listquotes[$i]['productid'] = $adb->query_result($resquotes, $i, 'productid');
        $fields_listquotes[$i]['quantity'] = $adb->query_result($resquotes, $i, 'quantity');
        $fields_listquotes[$i]['productno'] = $adb->query_result($resquotes, $i, 'productno');
        $fields_listquotes[$i]['productname'] = $adb->query_result($resquotes, $i, 'productname');
        $fields_listquotes[$i]['accountno'] = $adb->query_result($resquotes, $i, 'account_no');
        $fields_listquotes[$i]['accountname'] = $adb->query_result($resquotes, $i, 'accountname');
        $fields_listquotes[$i]['status'] = $adb->query_result($resquotes, $i, 'sostatus');
        $fields_listquotes[$i]['description'] = $adb->query_result($resquotes, $i, 'productsheet');
    }

    return $fields_listquotes;
    $log->debug("Exiting customerportal function get_list_cikabsalesorder");
}

/* Check VendorPortaluser by anil Singh */

function getVendorPortalUserid($portaluserid)
{
    global $adb, $log;
    $log->debug("Entering customer portal function getVendorPortalUserid");
    // Look the value from cache first
    $CurrentDate = date("Y-m-d");
    $res = $adb->pquery("SELECT cf_618 FROM vtiger_contactscf WHERE contactid = " . $portaluserid . " and cf_619 <= '" . $CurrentDate . "' and cf_620 >= '" . $CurrentDate . "'", array());
    $norows = $adb->num_rows($res);
    if ($norows > 0) {
        $Vendorportaluser = $adb->query_result($res, 0, 'cf_618');
    } else {
        $Vendorportaluser = 0;
    }
    return $Vendorportaluser;
    $log->debug("Exiting customerportal function getVendorPortalUserid");
}

/*  For Create Function by Anil Singh */

function get_list_cikabVendorPortal($id, $module, $sessionid, $only_mine = 'false', $status = "", $ACCID)
{
    require_once('modules/' . $module . '/' . $module . '.php');
    require_once('include/utils/UserInfoUtil.php');
    global $adb, $log, $current_user;
    $log->debug("Entering customer portal function get_list_cikabVendorPortal");
    $check = checkModuleActive($module);
    if ($check == false) {
        return array("#MODULE INACTIVE#");
    }
    $user = new Users();
    $userid = getPortalUserid();
    $current_user = $user->retrieveCurrentUserInfoFromFile($userid);
    if (!validateSession($id, $sessionid))
        return null;

    $ActiveVendorPortal = getVendorPortalUserid($id);
    if ($ActiveVendorPortal == false) {
        return array("2");
    }

    /*  Get Vendor to related Contact Id */
    $Vendor_id = array();
    $vendorlist = "SELECT vendorid FROM vtiger_vendorcontactrel WHERE contactid=?";
    $VendorParams = array($id);
    $VendorResult = $adb->pquery($vendorlist, array($VendorParams));
    $rowsvendor = $adb->num_rows($VendorResult);
    for ($i = 0; $i < $rowsvendor; $i++) {
        $Vendor_id[] = $adb->query_result($VendorResult, $i, 'vendorid');
    }
    
    $Wherecases = array();
    $Wherecases[] = "WHERE CE.deleted = 0 ";
    $Wherecases[] = " po.vendorid IN  (" . generateQuestionMarks($Vendor_id) . ") ";
    $paramsquotes = $Vendor_id;

    if (!empty($status)) {
        $Wherecases[] = "po.postatus='" . $status . "'";
    }

    $Where = implode(' AND ', $Wherecases);

    $queryvendorportal = "SELECT DISTINCT i.id,i.productid, i.quantity,p.product_no productno ,p.productname,ac.account_no,ac.accountname,po.postatus,p.productsheet FROM vtiger_inventoryproductrel i
					  INNER JOIN vtiger_crmentity CE on CE.crmid=i.id
					  INNER JOIN vtiger_products p on p.productid=i.productid
					  INNER JOIN vtiger_purchaseorder po on i.id=po.purchaseorderid
					  LEFT JOIN  vtiger_contactdetails cd on po.contactid=cd.contactid
					  INNER JOIN vtiger_account ac on cd.accountid=ac.accountid
                      " . $Where . "  ORDER BY ac.account_no ASC";

    $resquotes = $adb->pquery($queryvendorportal, array($Vendor_id));
    $rowsquotes = $adb->num_rows($resquotes);
    for ($i = 0; $i < $rowsquotes; $i++) {
        $fields_listquotes[$i]['quoteid'] = $adb->query_result($resquotes, $i, 'id');
        $fields_listquotes[$i]['productid'] = $adb->query_result($resquotes, $i, 'productid');
        $fields_listquotes[$i]['quantity'] = $adb->query_result($resquotes, $i, 'quantity');
        $fields_listquotes[$i]['productno'] = $adb->query_result($resquotes, $i, 'productno');
        $fields_listquotes[$i]['productname'] = $adb->query_result($resquotes, $i, 'productname');
        $fields_listquotes[$i]['accountno'] = $adb->query_result($resquotes, $i, 'account_no');
        $fields_listquotes[$i]['accountname'] = $adb->query_result($resquotes, $i, 'accountname');
        $fields_listquotes[$i]['status'] = $adb->query_result($resquotes, $i, 'postatus');
        $fields_listquotes[$i]['description'] = $adb->query_result($resquotes, $i, 'productsheet');
    }

    return $fields_listquotes;
    $log->debug("Exiting customerportal function get_list_cikabVendorPortal");
}

/**	function used to create ticket which has been created from customer portal
 *	@param array $input_array - array which contains the following values
 => 	int $id - customer id
	int $sessionid - session id
	string $title - title of the ticket
	string $description - description of the ticket
	string $priority - priority of the ticket
	string $severity - severity of the ticket
	string $category - category of the ticket
	string $user_name - customer name
	int $parent_id - parent id ie., customer id as this customer is the parent for this ticket
	int $product_id - product id for the ticket
	string $module - module name where as based on this module we will get the module owner and assign this ticket to that corresponding user
	*	return array - currently created ticket array, if this is not created then all tickets list will be returned
	*/
function create_custom_ticket($input_array)
{
    include 'modules/CikabTroubleTicket/CustomConfig.php';
    $custom_fields = CustomConfig::$custom_fields;
    
	global $adb,$log;
	$adb->println("Inside customer portal function create_ticket");
	$adb->println($input_array);
	$id = $input_array['id'];
	$sessionid = $input_array['sessionid'];
	$title = $input_array['title'];
	$description = $input_array['description'];
	$priority = $input_array['priority'];
	$severity = $input_array['severity'];
	$category = $input_array['category'];
	$user_name = $input_array['user_name'];
	$parent_id = (int) $input_array['parent_id'];
	$product_id = (int) $input_array['product_id'];
	$module = $input_array['module'];
	//$assigned_to = $input_array['assigned_to'];
	$servicecontractid = $input_array['serviceid'];
	$projectid = $input_array['projectid'];

    if (!validateSession($id, $sessionid))
        return null;

    $quoteid = $input_array['quoteid'];
    $product_no = $input_array['product_no'];
    $result = $adb->pquery("select productid from vtiger_products 
        where product_no = ?", array($product_no));
    $product_id = $adb->query_result($result, 0, 'productid');
    
    $ticket = new HelpDesk();

    $ticket->column_fields['ticket_title'] = $title;
    $ticket->column_fields['description'] = $description;
    $ticket->column_fields['ticketpriorities'] = $priority;
    $ticket->column_fields['ticketseverities'] = $severity;
    $ticket->column_fields['ticketcategories'] = $category;
    $ticket->column_fields['ticketstatus'] = 'Open';

    $ticket->column_fields['parent_id'] = $parent_id;
    $ticket->column_fields['product_id'] = $product_id;
    $ticket->column_fields[$custom_fields['product_quantity']] = $input_array['product_quantity'];
    
    if ($title == 'Release' || $title == 'Decrease')
        $ticket->column_fields[$custom_fields['increase_decrease']] = 'Decrease';
    elseif ($title == 'Increase')
        $ticket->column_fields[$custom_fields['increase_decrease']] = 'Increase';
    
    if(!empty($input_array['product_quantity']))
        $ticket->column_fields[$custom_fields['requested_date']] = date('Y-m-d');
    
    $defaultAssignee = getDefaultAssigneeId();

    $ticket->column_fields['assigned_user_id'] = $defaultAssignee;
    $ticket->column_fields['from_portal'] = 1;

	$ticket->save("HelpDesk");
    
	$ticketresult = $adb->pquery("select vtiger_troubletickets.ticketid from vtiger_troubletickets
		inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_troubletickets.ticketid 
        inner join vtiger_ticketcf on vtiger_ticketcf.ticketid = vtiger_troubletickets.ticketid 
		where vtiger_crmentity.deleted=0 and vtiger_troubletickets.ticketid = ?", array($ticket->id));
	if($adb->num_rows($ticketresult) == 1)
	{
		$record_save = 1;
		$record_array[0]['new_ticket']['ticketid'] = $adb->query_result($ticketresult,0,'ticketid');
	}
	if($servicecontractid != ''){
		$res = $adb->pquery("insert into vtiger_crmentityrel values(?,?,?,?)",
		array($servicecontractid, 'ServiceContracts', $ticket->id, 'HelpDesk'));
	}
	if($projectid != '') {
		$res = $adb->pquery("insert into vtiger_crmentityrel values(?,?,?,?)",
		array($projectid, 'Project', $ticket->id, 'HelpDesk'));		
	}
	if($record_save == 1)
	{
		$adb->println("Ticket from Portal is saved with id => ".$ticket->id);
		return $record_array;
	}
	else
	{
		$adb->println("There may be error in saving the ticket.");
		return null;
	}
}

/* ADDED BY PRABHAT KHERA 01-12-2012 */

/** 	function used to create sales order which has been created from customer portal
 * 	@param array $input_array - array which contains the following values
  => 	int $id - customer id
  int $sessionid - session id
  string $title - title of the ticket
  string $description - description of the ticket
  string $priority - priority of the ticket
  string $severity - severity of the ticket
  string $category - category of the ticket
  string $user_name - customer name
  string $customer_account_id - customer account id
  int    $product_id - product id for the sales order
  string $product_name - product name,
  int    $product_quantity - product quantity
  string $module - module name where as based on this module we will get the
  module owner and assign this sales order to that corresponding user
  return array - currently created sales order array, if this is not created
  then null will be returned
 */
function create_salesorder($input_array)
{
    global $adb, $log;
    $adb->println("Inside customer portal function create_salesorder");
    $adb->println($input_array);

    if (!validateSession($id, $sessionid))
        return null;

    $id = $input_array['id'];
    $sessionid = $input_array['sessionid'];
    $customer_id = $input_array['id'];
    $customer_account_id = $input_array['customer_account_id'];

    $defaultAssignee = getDefaultAssigneeId();

    $param['column_fields'] = array(
        'salesorder_no' => null,
        'subject' => 'Call-Off',
        'potential_id' => '',
        'customerno' => '',
        'quote_id' => '',
        'vtiger_purchaseorder' => '',
        'contact_id' => $customer_id,
        'duedate' => null,
        'carrier' => '',
        'pending' => '',
        'sostatus' => 'Created',
        'txtAdjustment' => '',
        'salescommission' => '',
        'exciseduty' => '',
        'hdnGrandTotal' => '',
        'hdnSubTotal' => '',
        'hdnTaxType' => '',
        'hdnDiscountPercent' => '',
        'hdnDiscountAmount' => '',
        'hdnS_H_Amount' => '',
        'account_id' => $customer_account_id,
        'assigned_user_id' => $defaultAssignee,
        'createdtime' => '',
        'modifiedtime' => '',
        'currency_id' => 1,
        'conversion_rate' => '',
        'bill_street' => '',
        'ship_street' => '',
        'bill_city' => '',
        'ship_city' => '',
        'bill_state' => '',
        'ship_state' => '',
        'bill_code' => '',
        'ship_code' => '',
        'bill_country' => '',
        'ship_country' => '',
        'bill_pobox' => '',
        'ship_pobox' => '',
        'description' => '',
        'terms_conditions' => '',
        'enable_recurring' => '',
        'recurring_frequency' => '',
        'start_period' => '',
        'end_period' => '',
        'payment_duration' => '',
        'invoicestatus' => '',
        'modifiedby' => '',
        'inventory_currency' => ''
    );

    $product_no = $input_array['product_no'];
    $result = $adb->pquery("select productid from vtiger_products 
        where product_no = ?", array($product_no));
    $productid = $adb->query_result($result, 0, 'productid');

    $_REQUEST = array('taxtype' => 'individual',
        'deleted1' => 0,
        'hidtax_row_no1' => '',
        'productName1' => $input_array['product_name'],
        'hdnProductId1' => $productid,
        'lineItemType1' => '',
        'subproduct_ids1' => '',
        'comment1' => '',
        'qty1' => $input_array['product_quantity'],
        'listPrice1' => '00.00',
        'discount_type1' => 'zero',
        'discount1' => 'on',
        'discount_percentage1' => '',
        'discount_amount1' => '',
        'action' => 'Save',
        'totalProductCount' => 1,
        'subtotal' => '0.00',
        'total' => '0.00');


    $salesorder = new SalesOrder();

    $userid = getPortalUserid();

    $param['column_fields']['assigned_user_id'] = $userid;
    $salesorder->column_fields = $param['column_fields'];

    $salesorder->save("SalesOrder");
    $log->debug("SALESORDER : " . $salesorder->id);
    if ($salesorder->id > 0) {
        $adb->println("SalesOrder from Portal is saved with id => " .
            $salesorder->id);
        $param['column_fields']['salesorderid'] = $salesorder->id;
        $result = $adb->pquery("select salesorder_no from vtiger_salesorder 
            where salesorderid = ?", array($salesorder->id));
        $salesorder_no = $adb->query_result($result, 0, 'salesorder_no');
        $param['column_fields']['salesorder_no'] = $salesorder_no;
        return $param;
    } else {
        $adb->println("There may be error in saving the SalesOrder.");
        return null;
    }
}
?>