<?php
include_once 'protected/extensions/language/' . $session['Lang'] . '.php';
?>
<?php
$this->pageTitle = Yii::app()->name . ' - Contacts ';
echo CHtml::metaTag($content = 'My page description', $name = 'decription');
$this->breadcrumbs = array(
    getTranslatedString('Contacts') . ' /' . getTranslatedString('add'),
);
?>
<h2>Create New Contact</h2>
<form enctype="multipart/form-data" method="POST" name="Addcontact">
    <table style="border:1px solid #589FC8;" cellspacing="0" cellpadding="3" width="100%" border="0" class="dvtContentSpace">

        <tr valign="top" style="height:25px">



            <td width="20%" align="right" class="dvtCellLabel">
                First Name						</td>

            <td width="30%" align="left" class="dvtCellInfo">
                <select  name="salutationtype">
                    <option value="--None--">
                        --None--
                    </option>
                    <option value="Mr.">
                        Mr.
                    </option>
                    <option value="Ms.">
                        Ms.
                    </option>
                    <option value="Mrs.">
                        Mrs.
                    </option>
                    <option value="Dr.">
                        Dr.
                    </option>
                    <option value="Prof.">
                        Prof.
                    </option>
                </select>
                <input type="text" value="" style="width:58%;" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" tabindex="" name="firstname">
            </td>

            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red">*</font>Last Name						</td>

            <td width="30%" align="left" class="dvtCellInfo">
                <input type="text" value="" style="width:58%;" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" tabindex="" name="lastname">
            </td>

        </tr>
        <tr valign="top" style="height:25px">

            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Office Phone </td>

            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="phone" name="phone" tabindex=""></td>
            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>Organization Name 			</td>
            <td width="30%" align="left" class="dvtCellInfo">
                <select style="width:150px" class="txtBox" id="bas_searchfield" name="account_name">
                    <option value="firstname" label="Organization1">Clab</option>
                    <option value="lastname" label="Organization2">Transport X</option>
                    <option value="Organization3" label="Organization3">Sample Organization</option>

                </select>
            </td>

        </tr>
        <tr valign="top" style="height:25px">
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mobile </td>

            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mobile" name="mobile" tabindex=""></td>
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Email </td>

            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="email" name="email" tabindex=""></td>

        </tr>
        <tr valign="top" style="height:25px">



            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Assistant </td>

            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="assistant" name="assistant" tabindex=""></td>



            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>Reports To 			</td>
            <td width="30%" align="left" class="dvtCellInfo">
                <select name="contact_name" id="contact_name">
                    <option value="Jonas">Jonas</option>
                    <option value="Lisa">Lisa</option>
                    <option value="Mary">Mary</option>
                    <option value="Prabhat">Prabhat</option>

                </select>
            </td>

        </tr>
        <tr valign="top" style="height:25px">



            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Assistant Phone </td>

            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="assistantphone" name="assistantphone" tabindex=""></td>


        </tr>


        <tr valign="top">
            <td class="detailedViewHeader" colspan="4">
                <b>Customer Portal Information</b>
            </td>
        </tr>

        <!-- Handle the ui types display -->


        <!-- Added this file to display the fields in Create Entity page based on ui types  -->
        <tr valign="top" style="height:25px">



            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>Portal User 			</td>

            <td width="30%" align="left" class="dvtCellInfo">
                <input type="hidden" value="" name="existing_portal">
                <input type="checkbox" tabindex="" name="portal">
            </td>



            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>Support Start Date 			</td>
            <td width="30%" align="left" class="dvtCellInfo">

                <input type="text" value="2014-01-22" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_support_start_date" tabindex="" name="support_start_date">
                <img id="jscal_trigger_support_start_date" src="themes/softed/images/btnL3Calendar.gif">




                <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>



            </td>

        </tr>
        <tr valign="top" style="height:25px">



            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>Support End Date 			</td>
            <td width="30%" align="left" class="dvtCellInfo">

                <input type="text" value="2015-01-21" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_support_end_date" tabindex="" name="support_end_date">
                <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>
            </td>

        </tr>
        <tr valign="top">
            <td class="detailedViewHeader" colspan="4">
                <b>Address Information</b></td>
        </tr>
        <tr valign="top" style="height:25px">



            <td width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>
                Mailing Street 			</td>
            <td width="30%" align="left" class="dvtCellInfo">
                <textarea rows="2" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" tabindex="" name="mailingstreet" value=""></textarea>
            </td>
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mailing PO Box </td>

            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mailingpobox" name="mailingpobox" tabindex=""></td>

        </tr>
        <tr valign="top" style="height:25px">
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mailing City </td>

            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mailingcity" name="mailingcity" tabindex=""></td>
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mailing State </td>

            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mailingstate" name="mailingstate" tabindex=""></td>

        </tr>
        <tr valign="top" style="height:25px">


            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mailing Postal Code </td>

            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mailingzip" name="mailingzip" tabindex=""></td>
            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mailing Country </td>

            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mailingcountry" name="mailingcountry" tabindex=""></td>

        </tr>
        <tr valign="top">
            <td valign="top" class="detailedViewHeader" colspan="4">
                <b>Description Information</b>
            </td>
        </tr>

        <tr valign="top" style="height:25px">


            <td valign="top" width="20%" align="right" class="dvtCellLabel">
                <font color="red"></font>
                Description 			</td>
            <td colspan="3">
                <textarea rows="8" cols="90" onblur="this.className = 'detailedViewTextBox'" name="description" onfocus="this.className = 'detailedViewTextBoxOn'" tabindex="" class="detailedViewTextBox"></textarea>
            </td>
        </tr>

        <tr valign="top">
            <td style="padding:5px" colspan="4">
                <div align="center">
                    <input type="submit" class="button" style="width:70px; margin-left: 10px !important;" value=" Save " name="submit">
                    <input type="button" class="button" style="width:70px; margin-left: 10px !important;" value="  Cancel  " name="cancel" onclick="window.history.back()">
                </div>
            </td>
        </tr>
    </table>
</form>