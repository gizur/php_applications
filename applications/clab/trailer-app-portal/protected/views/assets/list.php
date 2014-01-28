<?php
include_once 'protected/extensions/language/' . $session['Lang'] . '.php';
?>
<?php
$this->pageTitle = Yii::app()->name . ' - Assets List ';
echo CHtml::metaTag($content = 'My page description', $name = 'decription');
$this->breadcrumbs = array(
    getTranslatedString('Assets') . ' /' . getTranslatedString('Assets List'),
);

?>
<div id="wrap">
    <div class="toppanel">
        <table width="100%" border="0" cellspacing="0" cellpadding="1">
            <tr>
                <td valign="top">
                    <table width="100%" border="0" cellspacing="1" cellpadding="1" style="background:#FFF; border:#CCC solid 1px; padding:5px;">
                        <tr>
                            <td><strong>Asset No : </strong></td><td><input size="15pt" type="text" name="firstname" value="" /></td>
                            <td><strong>Asset Name : </strong></td><td><input size="15pt" type="text" name="lastname" value="" /></td>
                            <td><input type="submit" size="10pt" name="submit" value="Search" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div align="right"><a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=assets/add"><strong>Create New Asset</strong></a></div>
    <br />
    <div id="process">   
        <table id="table_id" class="dataTable" aria-describedby="table_id_info">
            <thead>
                <!-- Table Headers -->
                <tr role="row">

                    <th style="border-bottom: 1px solid #000000;">Asset No</th>
                    <th style="border-bottom: 1px solid #000000;">Asset Name</th>
                    <th style="border-bottom: 1px solid #000000;">Customer Name</th>
                    <th style="border-bottom: 1px solid #000000;">Product Name</th>
                    <th style="border-bottom: 1px solid #000000;">Serial Number</th>
                    <th style="border-bottom: 1px solid #000000;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['result'] as $data) { ?>
                    <tr class="odd">
                        <td><?php echo $data['asset_no']; ?> </td>
                        <td><?php echo $data['assetname']; ?></td>
                        <td><?php echo $resultAccounts[$data['account']]; ?></td>
                        <td><?php echo $resultProducts[$data['product']]; echo $data['product'];  ?></td>
                        <td><?php echo $data['serialnumber']; ?></td>
                        <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
       

    </div>
</div>
 <script type="text/javascript">
    jQuery(document).ready(function() {
       // jQuery("#assetsmsg").show().delay(5000).fadeOut();
        jQuery('#table_id').dataTable({
            "bStateSave": true
        });
    });

</script>
