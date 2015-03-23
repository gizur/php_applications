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
                                <option value="" selected="selected" ><?php echo getTranslatedString('-- Select --');  ?></option>
                                <?php foreach ($accounts['result'] as $accountsData) { ?>
                                    <option  value="<?php echo $accountsData['id']; ?>">
                                        <?php echo $accountsData['accountname']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>

                        <td>
                            <input type="submit" size="10pt" name="submit" value="<?php echo getTranslatedString('Search'); ?>" id="search" />
                        </td>
                    </tr>
                </table>
            </td>
            </tr>
        </table>
    </div>
    <div align="right"><a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=contacts/add" style="cursor: pointer;"><strong><?php echo getTranslatedString('Create New Contact'); ?></strong></a></div>
    <br />
    <div><span id='contactsmsg' style="position:fixed; margin:-15px 0 0 350px; "></span></div>
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
                        <td><?php echo $resultAccounts[$data['account_id']]; ?></td>
                        <td><?php echo $data['email']; ?></td>
                        <td><?php echo $data['phone']; ?></td>
                        <td><?php echo $resultUsers[$data['assigned_user_id']]; ?></td>
                        <td><a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=contacts/edit&id=<?php echo $data['id'];  ?>" contactId="<?php echo $data['id'];  ?>" id="edit"><?php echo getTranslatedString('edit'); ?></a>
                            | <a href='javascript:void(0);' contactId="<?php echo $data['id'];  ?>" id="delete">del</a>
                            | <a href='javascript:void(0);' id="resetPassword" email="<?php echo $data['email'];  ?>"><?php echo getTranslatedString('Reset Password'); ?></a>
| <a href='javascript:void(0);' id="resetUser" email="<?php echo $data['email'];  ?>"uid="<?php echo $data['id'];?>"lastname="<?php echo $data['lastname']; ?>"orgname="<?php echo $data['account_id']; ?>"reportto="<?php echo $data['contact_id'];?>" ><?php if($data['portal']==1){ echo getTranslatedString('Deactivate');}else{ echo getTranslatedString('Activate');} ?></a>
</td>

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
            var firstname = $.trim($("input[name='firstname']").val());
            var lastname = $.trim($("input[name='lastname']").val());
            var email = $.trim($("input[name='email']").val());
            var account = $.trim($("select[name='account']").val());
            $("#contactsmsg").addClass("waitprocess");
            $('#contactsmsg').html('loading....  Please wait');
            $.post('<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=contacts/searchcontacts',
                    {firstname: firstname, lastname: lastname, email:email, account:account},
            function(data) {
                $("#contactsmsg").removeClass("waitprocess");
                $('#contactsmsg').html('');
                $("#process").html(data);
            }
            );
        }
        $("#search").click(function() {
            filterAsset();
        });

        $("#delete").live('click', function() {
            var id = $(this).attr('contactId');
            if (confirm("<?php echo getTranslatedString('Are you sure to delete this data?'); ?>")) {
                $("#contactsmsg").addClass("waitprocess");
                $('#contactsmsg').html('loading....  Please wait');
                $.post('<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=contacts/delete',
                        {id: id},
                function(data) {
                    alert(data.msg);
                    filterAsset();
                }, 'json'
                        );
            }
        });
        
        $("#resetPassword").live('click', function() {
            var email = $(this).attr('email');
            if (confirm("<?php echo getTranslatedString('Are you sure to reset password?'); ?>")) {
                $("#contactsmsg").addClass("waitprocess");
                $('#contactsmsg').html('Please wait...');
                $.post('<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=contacts/resetpassword',
                        {email: email},
                function(data) {
                    alert(data.msg);
                    $("#contactsmsg").removeClass("waitprocess");
                    $('#contactsmsg').html('');
                }, 'json'
                        );
            }

        });
        
        $("#resetUser").live('click', function(e) {

if($(this).html()=="<?php echo getTranslatedString('wait..'); ?>"){
return false;
}
            var email = $(this).attr('email');

             var id = $(this).attr('uid');
             var lastname = $(this).attr('lastname');
var  account_id= $(this).attr('orgname');
var contact_id= $(this).attr('reportto');
var portal;
if($(this).html()=="<?php echo getTranslatedString('Deactivate'); ?>"){
 portal=0;
var mes="<?php echo getTranslatedString('Are you sure to deactivate user?'); ?>";
}else{
 portal=1;
var mes="<?php echo getTranslatedString('Are you sure to activate user?'); ?>";


}
 if (confirm(mes)) {
                //$("#resetUser").addClass("waitprocess");
                //$('#resetUser').html('Please wait...');
                  $(this).html("<?php echo getTranslatedString('wait..'); ?>");
                var self = this; 
                $.post('<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=contacts/UP',
                        {email: email, id: id, lastname: lastname, account_id: account_id, contact_id: contact_id, portal: portal},
                function(data) {
                  
                    if(data['result']['portal']=="0") {
                 $(self).html("<?php echo getTranslatedString('Activate'); ?>");
} else {
$(self).html("<?php echo getTranslatedString('Deactivate'); ?>");
}
                }, 'json'
                        );
            }

        });




    });

</script>

