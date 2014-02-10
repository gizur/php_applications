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
                        <tr><td colspan='3' align="center"></td></tr>
                        <tr>
                            <td><strong><?php echo getTranslatedString('Asset No'); ?>: </strong></td><td><input size="15pt" type="text" name="assetNo" value="" /></td>
                            <td><strong><?php echo getTranslatedString('Asset Name'); ?>: </strong></td><td><input size="15pt" type="text" name="assetName" value="" /></td>
                            <td><input type="submit" size="10pt" name="submit" value="<?php echo getTranslatedString('Search'); ?>" id="search" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div align="right"><a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=assets/add"><strong><?php echo getTranslatedString('Create New Asset');  ?></strong></a></div>
    <br />
    <div><span id='assetsmsg' style="position:fixed; margin:-15px 0 0 350px; "></span></div>
    <div id="process">
       
        <table id="table_id" class="dataTable" aria-describedby="table_id_info">
            <thead>
               <!-- Table Headers -->
                <tr role="row">

                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Asset No'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Asset Name'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Customer Name'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Product Name'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Serial Number'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['result'] as $data) { ?>
                    <tr class="odd">
                        <td><?php echo $data['asset_no']; ?> </td>
                        <td><?php echo $data['assetname']; ?></td>
                        <td><?php echo $resultAccounts[$data['account']]; ?></td>
                        <td><?php echo $resultProducts[$data['product']]; ?></td>
                        <td><?php echo $data['serialnumber']; ?></td>
                        <td><a href='<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=assets/edit&id=<?php echo $data['id'] ; ?>' id="edit" assetId="<?php echo $data['id']; ?>"><?php echo getTranslatedString('edit'); ?></a>  
                            | <a href='javascript:void(0);' id="delete" assetId="<?php echo $data['id']; ?>">del</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
 <script type="text/javascript">
    jQuery(document).ready(function() {
        //jQuery("#assetsmsg").show().delay(5000).fadeOut();
        jQuery('#table_id').dataTable({
            "bStateSave": true
        });
        function filterAsset() {
         var assetNo=$.trim($("input[name='assetNo']").val());
         var assetName=$.trim($("input[name='assetName']").val());
         $("#assetsmsg").addClass("waitprocess");
         $('#assetsmsg').html('loading....  Please wait');
         $.post('<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=assets/searchasset',
                {assetNo:assetNo,assetName:assetName},
                function(data) {
                    $("#assetsmsg").removeClass("waitprocess");
                    $('#assetsmsg').html('');
                    $("#process").html(data);
                    }
                );   
        }
        $("#search").click(function() {     
         filterAsset();
        });
        
        $("#delete").live('click',function() { 
             
            var id=$(this).attr('assetId');
            if(confirm("<?php echo getTranslatedString('Are you sure to delete this data?'); ?>")) {
                $("#assetsmsg").addClass("waitprocess");
                $('#assetsmsg').html('loading....  Please wait');
              $.post('<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=assets/delete',
                {id:id},
                function(data) {
                     alert(data.msg);
                     filterAsset();
                    }, 'json'
                );
             }
        
        });
        
    });

</script>
