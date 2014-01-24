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

                <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="assetname" name="assetname" tabindex=""></td>

                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>Type of Trailer
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <select tabindex="" name="cf_660">
                        <option selected="" value="Cooptrailer">
                            Cooptrailer
                        </option>
                        <option value="Hyrtrailer">
                            Hyrtrailer
                        </option>
                    </select>
                </td>
            </tr>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>Serial Number 			</td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" tabindex="" name="serialnumber">
                </td>
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>
                    Product Name
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="hidden" value="" name="product" id="product">
                    <select width="30%">
                        <option>Clab Trailer</option>
                    </select>
                </td>
            </tr>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>Date Sold 			</td>
                <td width="30%" align="left" class="dvtCellInfo">

                    <input type="text" value="2014-01-22" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_datesold" tabindex="" name="datesold">

                    <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
                </td>

                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>Date in Service
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text" value="2014-01-22" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_dateinservice" tabindex="" name="dateinservice">
                    <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
                </td>
            </tr>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red"></font>Shipping Method 			</td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" tabindex="" name="shippingmethod">
                </td>
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red"></font>Shipping Tracking Number 			</td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" tabindex="" name="shippingtrackingnumber">
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
                        <option selected="" value="In Service">
                            In Service
                        </option>
                        <option value="Out-of-service">
                            Out-of-service
                        </option>
                    </select>
                </td>
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red"></font>Tag Number 			</td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" tabindex="" name="tagnumber">
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