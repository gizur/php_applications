<?php
global $result, $client;
$customerid = $_SESSION['customer_id'];
$username = $_SESSION['customer_name'];
$sessionid = $_SESSION['customer_sessionid'];
$onlymine = $_REQUEST['onlymine'];
if ($onlymine == 'true') {
    $mine_selected = 'selected';
    $all_selected = '';
} else {
    $mine_selected = '';
    $all_selected = 'selected';
}

$module = 'CikabTroubleTicket';
$i = 0;
$params = Array('id' => $customerid, 'module' => $module, 'sessionid' => $sessionid, 'onlymine' => $onlymine);

$result = $client->call('get_list_preorder', $params);

if (!empty($result)) {
    foreach ($result as $value1) {
        $bal = $value1['totalquotes'] - $value1['totalsales'];
        if ($i % 2 == 0) {
            $class = "dvtLabel";
        } else {
            $class = "dvtInfo";
        }
        if (empty($value1['totalquotes'])) {
            $value1['totalquotes'] = '0';
        }
        if (empty($value1['totalsales'])) {
            $value1['totalsales'] = '0';
        }
        $accountname = $value1['accountname'];
        $list.="<tr class='" . $class . "'> 
            <td>" . $value1['productno'] . "</td><td>"
            . $value1['productname'] . "</td>
	        <td>" . $value1['productsheet'] . "</td>
	        <td>" . number_format($value1['totalquotes']) . "</td> 
	        <td>" . number_format($value1['totalsales']) . "</td>
	        <td>" . number_format($bal) . "</td>
	        <td>
	          <select name='saleaction' id='" . $value1['quoteid']
            . '_' . $value1['productname'] . "'
                  onchange=calllightbox(this.value,$bal,'{$value1['productno']}','{$value1['accountno']}',this.id,{$value1['quoteid']},'{$value1['productname']}')>
	          <option value=''>" . getTranslatedString('Select') . "</option>
	          <option value='Call off'>" . getTranslatedString('Call off') . "</option>
	          <option value='Release'>" . getTranslatedString('Release') . "</option>
	          <option value='Increase'>" . getTranslatedString('Increase') . "</option>
	          </select>
            </td>
	     </tr> ";
        $i++;
    }
} else {
    $list.="<tr><td colspan='6' align='center'>" . getTranslatedString('No record found') . "</td></tr>";
}
echo '<tr><td><span class="lvtHeaderText">'
 . getTranslatedString("Order") . '</span</td>';
$allow_all = $client->call('show_all', array('module' => 'Products'), $Server_Path, $Server_Path);
/// <option value="mine" '. $mine_selected .'>'.getTranslatedString('MINE').'</option> By Anil Singh
if ($allow_all == 'true') {
    echo '<td align="right" style="padding-right:50px;"> <b>' . getTranslatedString('SHOW') . '</b>&nbsp; 
        <select name="list_type" onchange="getList(this, \'CikabTroubleTicket\');">
	 			<option value="mine" ' . $mine_selected . '>' . getTranslatedString('MINE') . '</option>
				<option value="all"' . $all_selected . '>' . getTranslatedString('ALL') . '</option>
				</select></td></tr>';
}

echo '<tr><td colspan="2"><hr noshade="noshade" size="1" width="100%" align="left">
	      		<table width="95%"  border="0" cellspacing="0" cellpadding="5" align="center">';

echo '<tr align="center">
	      <td class="detailedViewHeader">' . getTranslatedString("Product Id") . '</td>
	      <td class="detailedViewHeader">' . getTranslatedString("Product Name") . '</td>
	      <td class="detailedViewHeader">' . getTranslatedString("Description") . '</td>
	      <td class="detailedViewHeader">' . getTranslatedString("Order") . '</td>
	      <td class="detailedViewHeader">' . getTranslatedString("Sale Order") . '</td>
	      <td class="detailedViewHeader">' . getTranslatedString("Left Order") . '</td>
	       <td class="detailedViewHeader">' . getTranslatedString("Action") . '</td>
	        </tr>' . $list . '
	   
	 </table>
</td></tr></table></td></tr></table>';
?>
<div id="dialog" title="Dialog Title">
    <form name="Save" id="Save" method="post" action="index.php" >
        <input type="hidden" name="module" value="CikabTroubleTicket" />
        <input type="hidden" name="action" value="index" />
        <input type="hidden" name="fun" value="saveticket" />
        <input type="hidden" name="productno" id="productno" />
        <input type="hidden" name="productname" id="productname" />
        <input type="hidden" name="accountno" id="accountno" />
        <input type="hidden" name="title" id="title" />
        <input type="hidden" name="accountname" id="accountname" value="<?php echo $accountname; ?>" />
        <input type="hidden" name="category" value="<?php echo getTranslatedString('Changing the pre-order'); ?>">
        <input type="hidden" name="bal" id="bal" value="" />
        <input type="hidden" name="quoteid" id="quoteid" value="" />
        <table>
            <tr>
                <td><span style="color :red">*</span> <?php echo getTranslatedString("Number"); ?> : </td>
                <td><input type='text' name='number' id='number' />
                </td>
            </tr>

        </table>

    </form>
</div>

<script>
    var __trans = {
        'Call off': '<?php echo getTranslatedString('Call off'); ?>',
        'Release': '<?php echo getTranslatedString('Release'); ?>',
        'Increase': '<?php echo getTranslatedString('Increase'); ?>'
    };
    function calllightbox(value, bal, prodno, accountno, tid, quoteid, productname)
    {
        var titlevalue = "";
        var prenumber = "";
        $('#number').val(prenumber)
        if (value != "")
        {
            $('#dialog').dialog('open');
            $('#title').val(value);
            $('#bal').val(bal);
            $('#productno').val(prodno);
            $('#productname').val(productname);
            $('#accountno').val(accountno);
            $('#quoteid').val(quoteid);

            titlevalue = __trans[value] + ' : ' + prodno + ' ' + productname;
            $('#ui-dialog-title-dialog').html(titlevalue);
            return false;
        }
    }
    function Checkvalidation()
    {
        var newquantity = parseInt(trim($('#number').val()));
        var balancequantity = parseInt(trim($('#bal').val()));
        var value = $('#title').val();
        if (trim($('#number').val()) == "")
        {
            alert("<?php echo getTranslatedString('The number box should not be empty'); ?>.");
            $('#number').focus();
            return false;
        }
        if ($('#number').val() != "")
        {
            if (!$('#number').val().match('^(0|[1-9][0-9]*)$'))
            {
                alert("<?php echo getTranslatedString('Field must be numeric'); ?>.");
                $('#number').focus();
                return false;
            }
            if (value != 'Increase')
            {
                if (newquantity > balancequantity)
                {
                    alert("<?php echo getTranslatedString('The number box should less than balance quantity'); ?>");
                    $('#number').focus();
                    return false;
                }
            }
        }
        return true;
    }
</script>
