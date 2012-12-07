
<?php

/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ****************************************************************************** */
require_once("include/Zend/Json.php");
@include("../PortalConfig.php");

global $result;
$username = $_SESSION['customer_name'];
$customerid = $_SESSION['customer_id'];
$sessionid = $_SESSION['customer_sessionid'];

if (!isset($_SESSION['customer_id']) || $_SESSION['customer_id'] == '') {
    @header("Location: $Authenticate_Path/login.php");
    exit;
}
include("index.html");
global $result;
$customerid = $_SESSION['customer_id'];

if ($_REQUEST['fun'] == 'saveticket') {

    include("CikabTroubleTicket/SavecikabTicket.php");
} elseif ($_REQUEST['fun'] == 'detail') {
    $ticketid = Zend_Json::decode($_REQUEST['ticketid']);
    $block = 'HelpDesk';
    include("TicketcikabDetail.php");
} elseif ($_REQUEST['fun'] == 'salesorder') {

    $salesorderid = Zend_Json::decode($_REQUEST['salesorderid']);
    $block = 'HelpDesk';
    include("SalesOrderDetail.php");
} else {
    include("SaleorderList.php");
}
?>
