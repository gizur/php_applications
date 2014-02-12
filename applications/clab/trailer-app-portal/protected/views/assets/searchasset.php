<?php
include_once 'protected/extensions/language/' . $session['Lang'] . '.php';
?>    

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
 <script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('#table_id').dataTable({
            "bStateSave": true
        });
        
    });

</script>

