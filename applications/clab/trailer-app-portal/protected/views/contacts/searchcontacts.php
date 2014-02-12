<?php
include_once 'protected/extensions/language/' . $session['Lang'] . '.php';
?>
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
                            | <a href='javascript:void(0)' contactId="<?php echo $data['id'];  ?>" id="delete">del</a>
                            | <a href='javascript:void(0)' id="resetPassword" email="<?php echo $data['email'];  ?>"><?php echo getTranslatedString('Reset Password'); ?></a></td>
                        </td>
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
