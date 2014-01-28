<?php
include_once 'protected/extensions/language/' . $session['Lang'] . '.php';
?>
<?php
$this->pageTitle = Yii::app()->name . ' - Assets List ';
echo CHtml::metaTag($content = 'My page description', $name = 'decription');
$this->breadcrumbs = array(
    getTranslatedString('Assets') . ' /' . getTranslatedString('Assets List'),
);
echo "<pre>";
print_r($result);
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
        <div id="table_id_wrapper" class="dataTables_wrapper" role="grid">

            <div align="left" id="table_id_length" class="dataTables_length">
                <label>Show 
                    <select name="table_id_length" size="1" aria-controls="table_id">
                        <option value="10" selected="selected">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select> entries
                </label>
            </div>
        </div>
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
            <?php foreach($result['result'] as $data) { ?>
            <tr class="odd">
                <td><?php echo $data['asset_no']; ?> </td>
                <td><?php echo $data['assetname']; ?></td>
                <td><?php echo $resultAccount[$data['account']]; ?></td>
                <td>Clab Trailer </td>
                <td>TRAILER5</td>
                <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a></td>
            </tr>
            <?php } ?>
            <!-- Table Contents -->
            <tr class="odd">
                <td>AST1001 </td>
                <td>AFN141F</td>
                <td>Clab</td>
                <td>Clab Trailer </td>
                <td>TRAILER5</td>
                <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a></td>
            </tr>
  
            <tr class="even">
                <td>AST1002</td>
                <td>AFU767F</td>
                <td>Clab</td>
                <td>Clab Trailer </td>
                <td>TRAILER6 </td>
                <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a></td>
            </tr>

            <tr class="odd">
                <td>AST1003</td>
                <td>AGD476F</td>
                <td>Clab</td>
                <td>Clab Trailer </td>
                <td> TRAILER9</td>
                <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a></td>
            </tr>
           </tbody>
        </table>
        <div id="table_id_info" class="dataTables_info" style="width:100%">Showing 1 to 3 of 3 entries
            <span style=" float:right;" id="table_id_paginate" class="dataTables_paginate paging_two_button" >
                <a aria-controls="table_id" id="table_id_previous" class="paginate_disabled_previous" tabindex="0" role="button">Previous</a>
                <a aria-controls="table_id" id="table_id_next" class="paginate_disabled_next" tabindex="0" role="button">Next</a>
            </span>
        </div>

    </div>
</div>
<script>
    function createNew() {
        $('#wrap').hide();
        $('#createcontact').show();
    }
    $(document).ready(function() {
        $('#createcontact').hide();
    });
</script>
