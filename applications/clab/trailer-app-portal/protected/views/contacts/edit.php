<?php
include_once 'protected/extensions/language/' . $session['Lang'] . '.php';
?>
<?php
$this->pageTitle = Yii::app()->name . ' - Contacts ';
echo CHtml::metaTag($content = 'My page description', $name = 'decription');
$this->breadcrumbs = array(
    getTranslatedString('Contacts') . ' /' . getTranslatedString('Update'),
);
?>
<h2><?php echo getTranslatedString('Update Contact'); ?></h2>
<form  action="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=contacts/edit" method="POST" name="contactsAdd" id="contactsAdd">
    <input type="hidden" value="<?php echo $result['id']; ?>" name="id" >
    <table style="border:1px solid #589FC8;" cellspacing="0" cellpadding="3" width="100%" border="0" class="dvtContentSpace">
        <tr valign="top" style="height:25px">
            <td width="20%" align="right" class="dvtCellLabel">
                <?php echo getTranslatedString('First Name'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <select  name="salutationtype">
                    <?php foreach ($salutations as $k => $sal) { ?>
                        <option  value="<?php echo $k; ?>">
                            <?php echo getTranslatedString($sal); ?>
                        </option>
                    <?php } ?>
                </select>
                <input type="text"  style="width:58%;"  tabindex="" name="firstname" value="<?php echo $result['firstname']; ?>">
            </td>
            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red">*</font><?php echo getTranslatedString('Last Name'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <input type="text"  style="width:58%;"  tabindex="" name="lastname" value="<?php echo $result['lastname']; ?>">
            </td>
        </tr>
        <tr valign="top" style="height:25px">

            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>
                <?php echo getTranslatedString('Office Phone'); ?>
            </td>

            <td width="30%" align="left" class="dvtCellInfo">
                <input type="text"  value="<?php echo $result['phone']; ?>" id="phone" name="phone" tabindex="">
            </td>
            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red">*</font>
                <?php echo getTranslatedString('Organization Name'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <select style="width:150px" class="txtBox" id="bas_searchfield" name="account_id">
                    <option value="" selected="selected" >
                        <?php echo getTranslatedString('-- Select --'); ?>
                    </option>
                    <?php foreach ($accounts['result'] as $accountsData) { ?>
                        <option  value="<?php echo $accountsData['id']; ?>" <?php if($accountsData['id'] == $result['account_id']) { echo "selected = 'selected'"; } ?>>
                            <?php echo $accountsData['accountname']; ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr valign="top" style="height:25px">
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>
                <?php echo getTranslatedString('Mobile'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <input type="text"  value="<?php echo $result['mobile']; ?>" id="mobile" name="mobile" tabindex="">
            </td>
            <td width="20%" align="right" class="dvtCellLabel"><font color="red">*</font>
                <?php echo getTranslatedString('Email'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <input type="text"  value="<?php echo $result['email']; ?>" id="email" name="email" tabindex="">
            </td>
        </tr>
        <tr valign="top" style="height:25px">
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>
                <?php echo getTranslatedString('Assistant'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <input type="text"  value="<?php echo $result['assistant']; ?>" id="assistant" name="assistant" tabindex="">
            </td>
            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red">*</font>
                <?php echo getTranslatedString('Reports To'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <select name="contact_id" id="contact_id">
                    <option value=""><?php echo getTranslatedString('-- Select --'); ?></option>
                    <?php foreach ($contacts['result'] as $contact) { ?>
                        <option  value="<?php echo $contact['id']; ?>" <?php if($contact['id'] == $result['contact_id']) { echo "selected = 'selected'"; } ?>>
                            <?php echo $contact['firstname'] . " " . $contact['lastname']; ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr valign="top" style="height:25px">
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>
                <?php echo getTranslatedString('Assistant Phone'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <input type="text"  value="<?php echo $result['assistantphone']; ?>" id="assistantphone" name="assistantphone" tabindex="">
            </td>
        </tr>
        <tr valign="top">
            <td class="detailedViewHeader" colspan="4">
                <b><?php echo getTranslatedString('Customer Portal Information'); ?></b>
            </td>
        </tr>
        <tr valign="top" style="height:25px">
            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>
                <?php echo getTranslatedString('Portal User'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <input type="hidden" value="" name="existing_portal">
                <input type="checkbox" tabindex="" name="portal" <?php if($result['portal']) { echo "checked = 'checked'"; } ?>>
            </td>
            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>
                <?php echo getTranslatedString('Support Start Date'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <input type="text" value="<?php echo $result['support_start_date']; ?>" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_support_start_date" tabindex="" name="support_start_date">
                <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
            </td>
        </tr>
        <tr valign="top" style="height:25px">
            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>
                <?php echo getTranslatedString('Support End Date'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <input type="text" value="<?php echo $result['support_end_date']; ?>" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_support_end_date" tabindex="" name="support_end_date">
                <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
            </td>
        </tr>
        <tr valign="top">
            <td class="detailedViewHeader" colspan="4">
                <b><?php echo getTranslatedString('Address Information'); ?></b>
            </td>
        </tr>
        <tr valign="top" style="height:25px">
            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>
                <?php echo getTranslatedString('Mailing Street'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <textarea rows="2"  tabindex="" name="mailingstreet" value="<?php echo $result['mailingstreet']; ?>"></textarea>
            </td>
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>
                <?php echo getTranslatedString('Mailing PO Box'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo">
                <input type="text"  value="<?php echo $result['mailingpobox']; ?>" id="mailingpobox" name="mailingpobox" tabindex=""></td>

        </tr>
        <tr valign="top" style="height:25px">
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>
                <?php echo getTranslatedString('Mailing City'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo"><input type="text"  value="<?php echo $result['mailingcity']; ?>" id="mailingcity" name="mailingcity" tabindex=""></td>
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>
                <?php echo getTranslatedString('Mailing State'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo"><input type="text"  value="<?php echo $result['mailingstate']; ?>" id="mailingstate" name="mailingstate" tabindex=""></td>

        </tr>
        <tr valign="top" style="height:25px">
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>
                <?php echo getTranslatedString('Mailing Postal Code'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo"><input type="text"  value="<?php echo $result['mailingzip']; ?>" id="mailingzip" name="mailingzip" tabindex=""></td>
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>
                <?php echo getTranslatedString('Mailing Country'); ?>
            </td>
            <td width="30%" align="left" class="dvtCellInfo"><input type="text"  value="<?php echo $result['mailingcountry']; ?>" id="mailingcountry" name="mailingcountry" tabindex=""></td>

        </tr>
        <tr valign="top">
            <td valign="top" class="detailedViewHeader" colspan="4">
                <b><?php echo getTranslatedString('Description Information'); ?></b>
            </td>
        </tr>
        <tr valign="top" style="height:25px">
            <td valign="top" width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>
                <?php echo getTranslatedString('Description'); ?>
            </td>
            <td colspan="3">
                <textarea rows="8" cols="90"  name="description" ><?php echo $result['description']; ?></textarea>
            </td>
        </tr>
        <tr valign="top">
            <td style="padding:5px" colspan="4">
                <div align="center">
                    <input type="submit" class="button" style="width:70px; margin-left: 10px !important;" value=" <?php echo getTranslatedString('Update'); ?> " name="submit">
                    <input type="button" class="button" style="width:70px; margin-left: 10px !important;" value="  <?php echo getTranslatedString('Cancel'); ?>  " name="cancel" onclick="window.history.back()">
                </div>
            </td>
        </tr>
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
        $("#contactsAdd").validate({
            rules: {
                lastname: 'required',
                account_id: 'required',
                email: 'required',
                contact_id: 'required'
            }

        });

    });
</script>
