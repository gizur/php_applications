<?php
include_once 'protected/extensions/language/' . $session['Lang'] . '.php';
?>
<?php
$this->pageTitle = Yii::app()->name . ' - Update Asset';
echo CHtml::metaTag($content = 'My page description', $name = 'decription');
$this->breadcrumbs = array(
    getTranslatedString('Assets') . ' /' . getTranslatedString('Update'),
);
?>
<h2><?php echo getTranslatedString('Update Asset');  ?></h2>
<form  action="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=assets/update" method="POST" name="assetCreate" id="assetCreate">
    <input type="hidden" value="<?php echo $result['id']; ?>" name="id">
    <table style="border:1px solid #589FC8;"  cellspacing="0" cellpadding="0" width="95%" border="0" align="center">
        <tbody>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel"><font color="red">*</font><?php echo getTranslatedString('Asset Name');  ?> </td>

                <td width="30%" align="left" class="dvtCellInfo"><input type="text"  class="detailedViewTextBox" value="<?php echo $result['assetname']; ?>" id="assetname" name="assetname" tabindex=""></td>

                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font><?php echo getTranslatedString('Type of Trailer');  ?>
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <select tabindex="" name="trailertype">
                        <option value="" selected="selected"><?php echo getTranslatedString('-- Select --');  ?></option>
                        <?php foreach ($trailerType['result'] as $trailer) { ?>
                            <option value="<?php echo $trailer['value']; ?>" <?php if($trailer['value'] == $result['trailertype']) { echo "selected = 'selected'"; } ?>>
                                <?php echo $trailer['label']; ?>
                            </option>
                        <?php } ?>

                    </select>
                </td>
            </tr>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font><?php echo getTranslatedString('Serial Number');  ?> </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text"  class="detailedViewTextBox" value="<?php echo $result['serialnumber']; ?>" tabindex="" name="serialnumber">
                </td>
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>
                    <?php echo getTranslatedString('Product Name');  ?>
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <select width="30%" name="product">
                        <option value="" selected="selected"><?php echo getTranslatedString('-- Select --');  ?></option>
                        <?php foreach ($products['result'] as $productsData) { ?>
                            <option value="<?php echo $productsData['id']; ?>" <?php if($productsData['id'] == $result['product']) { echo "selected = 'selected'"; } ?>>
                                <?php echo $productsData['productname']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font><?php echo getTranslatedString('Date Sold');  ?> </td>
                <td width="30%" align="left" class="dvtCellInfo">

                    <input type="text" value="<?php echo $result['datesold']; ?>" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_datesold" tabindex="" name="datesold">

                    <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
                </td>

                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font><?php echo getTranslatedString('Date in Service');  ?>
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text" value="<?php echo $result['dateinservice']; ?>" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_dateinservice" tabindex="" name="dateinservice">
                    <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
                </td>
            </tr>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red"></font><?php echo getTranslatedString('Shipping Method');  ?> 
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text"  value="<?php echo $result['shippingmethod']; ?>" tabindex="" name="shippingmethod">
                </td>
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red"></font><?php echo getTranslatedString('Shipping Tracking Number');  ?>  			
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <input type="text"  value="<?php echo $result['shippingtrackingnumber']; ?>" tabindex="" name="shippingtrackingnumber">
                </td>
            </tr>
            <tr style="height:25px">                        
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>
                    Status
                </td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <select class="" tabindex="" name="assetstatus">
                        <option value="" selected="selected"><?php echo getTranslatedString('-- Select --');  ?></option>
                        <?php foreach ($assetstatus['result'] as $status) { ?>
                            <option  value="<?php echo $status['value']; ?>" <?php if($status['value'] == $result['assetstatus']) { echo "selected = 'selected'"; } ?>>
                                <?php echo $status['label']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
                <td width="20%" align="right" class="dvtCellLabel">
                    <font color="red">*</font>Customer Name 			</td>
                <td width="30%" align="left" class="dvtCellInfo">
                    <select class="" tabindex="" name="account">
                        <option value="" selected="selected" ><?php echo getTranslatedString('-- Select --');  ?></option>
                        <?php foreach ($accounts['result'] as $accountsData) { ?>
                            <option  value="<?php echo $accountsData['id']; ?>" <?php if($accountsData['id'] == $result['account']) { echo "selected = 'selected'"; } ?>>
                                <?php echo $accountsData['accountname']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="detailedViewHeader" colspan="4">
                    <b><?php echo getTranslatedString('Notes');  ?></b>
                </td>
            </tr>
            <tr style="height:25px">
                <td width="20%" align="right" class="dvtCellLabel" style="vertical-align: middle;">
                    <font color="red"></font>
                    <?php echo getTranslatedString('Notes');  ?>
                </td>
                <td colspan="3">
                    <textarea rows="8" cols="90"  name="description"  tabindex="" class="detailedViewTextBox"><?php echo $result['description']; ?></textarea>
                </td>
            </tr>
            <tr>
                <td style="padding:5px" colspan="4">
                    <div align="center">
                        <input type="submit" class="button" style="width:70px; margin-left: 10px !important;" value="<?php echo getTranslatedString('Update');  ?>" name="submit">
                        <input type="button" class="button" style="width:70px; margin-left: 10px !important;" value="  <?php echo getTranslatedString('Cancel');  ?>  " name="cancel" onclick="return can();">
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</form>
<style>
.error {
color:red;
}
</style>
<!-- Form Validation script -->
<script>
     $(document).ready(function() {
         $("#assetCreate").validate({
                                rules:{
                                assetname:'required',
                                serialnumber:'required',
                                datesold:'required',
                                dateinservice:'required',
                                trailertype:'required',
                                assetstatus:'required',
                                product:'required',
                                account:'required',
                                }

                            });

    });
</script>

<script>
    function can() {
        location.href = "<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=assets/list";
    }
</script>