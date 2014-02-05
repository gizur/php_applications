<?php
include_once 'protected/extensions/language/' . $session['Lang'] . '.php';
?>
<?php
$this->pageTitle = Yii::app()->name . ' - Contacts List ';
echo CHtml::metaTag($content = 'My page description', $name = 'decription');
$this->breadcrumbs = array(
    getTranslatedString('Contacts') . ' /' . getTranslatedString('Contacts List'),
);
?>

<div id="wrap">
    <div class="toppanel">
        <table width="100%" border="0" cellspacing="0" cellpadding="1">
            <td valign="top">
                <table width="100%" border="0" cellspacing="1" cellpadding="1" style="background:#FFF; border:#CCC solid 1px; padding:5px;">
                    <tr>
                        <td><strong><?php echo getTranslatedString('First Name'); ?> : </strong></td><td><input size="15pt" type="text" name="firstname" value="" /></td>
                        <td><strong><?php echo getTranslatedString('Last Name'); ?> : </strong></td><td><input size="15pt" type="text" name="lastname" value="" /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo getTranslatedString('Email'); ?> : </strong></td><td><input size="15pt" type="text" name="email" value="" /></td>
                        <td><strong><?php echo getTranslatedString('Organization'); ?> : </strong>
                        </td>
                        <td>
                            <select class="" tabindex="" name="account">
                                <option value="" selected="selected" >-- Select --</option>
                                <?php foreach ($accounts['result'] as $accountsData) { ?>
                                    <option  value="<?php echo $accountsData['id']; ?>">
                                        <?php echo $accountsData['accountname']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>

                        <td>
                            <input type="submit" size="10pt" name="submit" value="Search" />
                        </td>

                    </tr>
                </table>
            </td>
            </tr>
        </table>
    </div>
    <div align="right"><a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=contacts/add" style="cursor: pointer;"><strong>Create New Contact</strong></a></div>
    <br />
    <div id="process">

        <table id="table_id" class="dataTable" aria-describedby="table_id_info">
            <thead>


                <!-- Table Headers -->
                <tr role="row">

                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Contact Id'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('First Name'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Last Name'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Title'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Organization Name'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Email'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Office Phone'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Assigned To'); ?></th>
                    <th style="border-bottom: 1px solid #000000;"><?php echo getTranslatedString('Action'); ?></th>
                </tr>
            </thead>
            <!-- Table Contents -->
            <tbody>
                <?php foreach ($result['result'] as $data) { ?>
                    <tr class="odd">
                        <td><?php echo $data['contact_no']; ?></td>
                        <td><?php echo $data['firstname']; ?></td>
                        <td><?php echo $data['lastname']; ?></td>
                        <td><?php echo $data['title']; ?></td>
                        <td><?php echo $resultAccounts[$data['account']];  ?></td>
                        <td><?php echo $data['email']; ?></td>
                        <td><?php echo $data['phone']; ?></td>
                        <td><?php echo $data['contact_no']; ?></td>
                        <td><a href="#">edit</a>  | <a href='javascript:void(0)'>del</a>  | <a href='javascript:void()'>Reset Password</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('#table_id').dataTable({
            "bStateSave": true
        });
        function filterAsset() {
            var assetNo = $.trim($("input[name='assetNo']").val());
            var assetName = $.trim($("input[name='assetName']").val());
            $("#assetsmsg").addClass("waitprocess");
            $('#assetsmsg').html('loading....  Please wait');
            $.post('<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=assets/searchasset',
                    {assetNo: assetNo, assetName: assetName},
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

        $("#delete").live('click', function() {

            var id = $(this).attr('assetId');
            if (confirm("Are you sure to delete this data?")) {
                $("#assetsmsg").addClass("waitprocess");
                $('#assetsmsg').html('loading....  Please wait');
                $.post('<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=assets/delete',
                        {id: id},
                function(data) {
                    alert(data.msg);
                    filterAsset();
                }, 'json'
                        );
            }

        });

    });

</script>

