<?php
include_once 'protected/extensions/language/' . $session['Lang'] . '.php';
?>
<?php
$this->pageTitle = Yii::app()->name . ' - Creating New Asset';
echo CHtml::metaTag($content = 'My page description', $name = 'decription');
$this->breadcrumbs = array(
    getTranslatedString('Assets') . ' /' . getTranslatedString('Creating New Asset'),
);
?>
<h2>Create New Asset</h2>
<form onsubmit="VtigerJS_DialogBox.block();" action="index.php" method="POST" name="EditView">
    <table style="border:1px solid #589FC8;"  cellspacing="0" cellpadding="0" width="95%" border="0" align="center">
        <tbody>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel"><font color="red">*</font>Asset Name </td>

                <td width="30%" align="left" class="dvtCellInfo"><input type="text"  class="detailedViewTextBox" value="" id="assetname" name="assetname" tabindex=""></td>

                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>Type of Trailer
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <select tabindex="" name="trailertype">
                        <option value="0" selected="selected">-- Select --</option>
                        <?php foreach ($trailerType['result'] as $trailer) { ?>
                            <option value="<?php echo $trailer['value']; ?>">
                                <?php echo $trailer['label']; ?>
                            </option>
                        <?php } ?>

                    </select>
                </td>
            </tr>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>Serial Number 			</td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text"  class="detailedViewTextBox" value="" tabindex="" name="serialnumber">
                </td>
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>
                    Product Name
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="hidden" value="" name="product" id="product">
                    <select width="30%">
                        <option value="0" selected="selected">-- Select --</option>
                        <?php foreach ($products['result'] as $productsData) { ?>
                            <option value="<?php echo $productsData['id']; ?>">
                                <?php echo $productsData['productname']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>Date Sold 			</td>
                <td width="30%" align="left" class="dvtCellInfo">

                    <input type="text" value="<?php echo date('Y-m-d'); ?>" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_datesold" tabindex="" name="datesold">

                    <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
                </td>

                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>Date in Service
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text" value="<?php echo date('Y-m-d'); ?>" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_dateinservice" tabindex="" name="dateinservice">
                    <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
                </td>
            </tr>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red"></font>Shipping Method 
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text"  value="" tabindex="" name="shippingmethod">
                </td>
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red"></font>Shipping Tracking Number 			
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text"  value="" tabindex="" name="shippingtrackingnumber">
                </td>
            </tr>
            <tr style="height:25px">                        
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>
                    Status
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="hidden" value="" name="product" id="product">
                    <select class="" tabindex="" name="assetstatus">
                        <option value="0" selected="selected">-- Select --</option>
                        <?php foreach ($assetstatus['result'] as $status) { ?>
                            <option selected="" value="<?php echo $status['value']; ?>">
                                <?php echo $status['label']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>Customer Name 			</td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <select class="" tabindex="" name="assetstatus">
                        <option value="0" selected="selected">-- Select --</option>
                        <?php foreach ($accounts['result'] as $accountsData) { ?>
                            <option selected="" value="<?php echo $accountsData['id']; ?>">
                                <?php echo $accountsData['accountname']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="detailedViewHeader" colspan="4">
                    <b>Notes</b>
                </td>
            </tr>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel" style="vertical-align: middle;">
                    <font color="red"></font>
                    Notes
                </td>
                <td colspan="3">
                    <textarea rows="8" cols="90" onblur="this.className = 'detailedViewTextBox'" name="description" onfocus="this.className = 'detailedViewTextBoxOn'" tabindex="" class="detailedViewTextBox"></textarea>
                </td>
            </tr>
            <tr>
                <td style="padding:5px" colspan="4">
                    <div align="center">
                        <input type="button" class="button" style="width:70px; margin-left: 10px !important;" value=" Save " name="submit">
                        <input type="button" class="button" style="width:70px; margin-left: 10px !important;" value="  Cancel  " name="cancel" onclick="return can();">
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</form>
<script>
    function can() {
        location.href = "<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=assets/list";
    }
</script>