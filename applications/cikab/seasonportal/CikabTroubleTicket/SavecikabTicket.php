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

global $client;
global $result;

$ticket = Array(
    'title' => 'title',
    'productid' => 'productid',
    'description' => 'description',
    'priority' => 'priority',
    'category' => 'category',
    'owner' => 'owner',
    'module' => 'module'
);

foreach ($ticket as $key => $val)
    $ticket[$key] = $_REQUEST[$key];

$ticket['owner'] = $username;
$ticket['productid'] = $_SESSION['combolist'][0]['productid'][$ticket['productid']];

$title = $_REQUEST['title'];
$description = $_REQUEST['description'];
$description.="" . getTranslatedString('Product Id') . " : " . $_REQUEST['productno'] .
    " , " . getTranslatedString('Product Name') . " : " . $_REQUEST['productname'];
$description.=" , " . getTranslatedString('Account Id') . " : " . $_REQUEST['accountno'] . " , "
    . getTranslatedString('Quantity') . " : " . $_REQUEST['number'];
$priority = $_REQUEST['priority'];
$severity = $_REQUEST['severity'];
$category = $_REQUEST['category'];
$parent_id = $_SESSION['customer_id'];
$productid = $_SESSION['combolist'][0]['productid'][$_REQUEST['productid']];

$module = $_REQUEST['ticket_module'];

$customerid = $_SESSION['customer_id'];
$sessionid = $_SESSION['customer_sessionid'];
$serviceid = $_REQUEST['servicename'];
$customer_account_id = $_SESSION['customer_account_id'];
$projectid = $_REQUEST['projectid'];

$newquantity = $_REQUEST['number'];
$balancequantity = $_REQUEST['bal'];
 if ($newquantity > $balancequantity)
   {
    echo '<table><tr><td><input class="crmbutton small cancel" type="button" value="'.getTranslatedString('LBL_BACK_BUTTON').'" onclick="window.history.back();"/></td></tr></table>';
    echo "<font color=red>Error: "
    .getTranslatedString('The number box should less than balance quantity')
    ."</font>";
    exit;            
   }

$params = Array(Array(
        'id' => "$customerid",
        'sessionid' => "$sessionid",
        'title' => "$title",
        'description' => "$description",
        'priority' => "$priority",
        'severity' => "$severity",
        'category' => "$category",
        'user_name' => "$username",
        'parent_id' => "$parent_id",
        'product_id' => "$productid",
        'module' => "$module",
        'assigned_to' => "$Ticket_Assigned_to",
        'serviceid' => "$serviceid",
        'projectid' => "$projectid",
        'customer_account_id' => "$customer_account_id",
        'product_name' => $_REQUEST['productname'],
        'product_quantity' => $_REQUEST['number'],
        'product_no' => $_REQUEST['productno']
    ));

/* Create SalesOrder only on Call Off and for increase or decrease create TT */
if ($title == 'Call off') {
    $record_result = $client->call('create_salesorder', $params);
    ?>
    <script>
        var salesorder_no = '<?php echo $record_result[0]['salesorder_no']; ?>';
        window.location.href = "index.php?module=CikabTroubleTicket&" + 
            "action=index&fun=salesorder&salesorder_no="+salesorder_no;
    </script>
    <?php
} else {
    $record_result = $client->call('create_custom_ticket', $params);
    if (isset($record_result[0]['new_ticket']) && 
        $record_result[0]['new_ticket']['ticketid'] != '') {
        $new_record = 1;
        $ticketid = $record_result[0]['new_ticket']['ticketid'];
    }
    if ($new_record == 1) {
        ?>
        <script>
            var ticketid = '<?php echo $ticketid; ?>';
            window.location.href = "index.php?module=CikabTroubleTicket" + 
                "&action=index"
        </script>
        <?php
    } else {
        echo getTranslatedString('LBL_PROBLEM_IN_TICKET_SAVING');
        //include("NewTicket.php");
        echo "</tr>
        </table>
        </td>
        </tr>
        </table>";
    }
}
?>
