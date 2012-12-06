<?php
$title=$_REQUEST['title'];
$productid=base64_decode($_REQUEST['pid']);
$productname=base64_decode($_REQUEST['pname']);
$accountid=base64_decode($_REQUEST['acno']);
$balanceorder=base64_decode($_REQUEST['bal']);
?>
<form name="salesorderrequest" id="salesorderrequest" action="saveorder.php" method="post">
<table>
	<tr>
	<td colspan="2" align="center"><?php echo ucwords($title); ?></td>
	</tr>
<tr>
<td>Antal : </td>
<td><input type='text' name='number' id='number' />
</td>
</tr
<tr>
<td><input type="button" name="submit" value="OK">&nbsp;<input type="button" name="cancel" value="Ängra" onclick="window.close()"></td>
</tr> 
</table>
<input type="hidden" name="title" id="title" value="<?php echo $title; ?>">
<input type="hidden" name="productid" id="productid" value="<?php echo $productid; ?>">
<input type="hidden" name="productid" id="productid" value="<?php echo $productid; ?>">
<input type="hidden" name="productname" id="productname" value="<?php echo $productname; ?>">
<input type="hidden" name="accountid" id="accountid" value="<?php echo $accountid; ?>">

</form>
