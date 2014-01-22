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
<div id="content">
    <div id="wrap">
        <div class="toppanel">
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
                <td valign="top">
                    <table width="100%" border="0" cellspacing="1" cellpadding="1" style="background:#FFF; border:#CCC solid 1px; padding:5px;">
                        <tr>
                            <td><strong>First Name : </strong></td><td><input size="15pt" type="text" name="firstname" value="" /></td>
                            <td><strong>Last Name : </strong></td><td><input size="15pt" type="text" name="lastname" value="" /></td>
                            <td><strong>Email : </strong></td><td><input size="15pt" type="text" name="email" value="" /></td>
                        </tr>
                        <tr>
                            <td><strong>Organization : </strong>
                            </td>
                            <td>
                                <select style="width:150px" class="txtBox" id="bas_searchfield" name="orgname">
                                    <option value="firstname" label="Organization1">Clab</option>
                                    <option value="lastname" label="Organization2">Transport X</option>
                                    <option value="Organization3" label="Organization3">Sample Organization</option>
                                </select>
                            </td>
                            <td>
                                <input type="submit" size="10pt" name="submit" value="Search" />
                            </td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                </td>
                </tr>
            </table>
        </div>
        <div align="right"><a href="javascript:void(0)" style="cursor: pointer;" onclick="createNew()"><strong>Create New Contact</strong></a></div>
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
    <!--            <span style="text-align: right; margin-left: 450px;" >    <strong>Search :</strong> 
                    <input type="text" name="searchtext" value="" />
                </span>-->
                </div>
            </div>
            <table id="table_id" class="dataTable" aria-describedby="table_id_info">


                <!-- Table Headers -->
                <tr role="row">

                    <th style="border-bottom: 1px solid #000000;">Contact Id</th>
                    <th style="border-bottom: 1px solid #000000;">First Name</th>
                    <th style="border-bottom: 1px solid #000000;">Last Name</th>
                    <th style="border-bottom: 1px solid #000000;">Title</th>
                    <th style="border-bottom: 1px solid #000000;">Organization Name</th>
                    <th style="border-bottom: 1px solid #000000;">Email</th>
                    <th style="border-bottom: 1px solid #000000;">Office Phone</th>
                    <th style="border-bottom: 1px solid #000000;">Assigned To</th>
                    <th style="border-bottom: 1px solid #000000;">Action</th>
                </tr>
                <!-- Table Contents -->
                <tr class="odd">

                    <td>Mary</td>
                    <td>Smith</td>
                    <td>Mgr Operations</td>
                    <td>VP Operations</td>
                    <td>t3M Invest A/S</td>
                    <td>mary_smith@company.com</td>
                    <td>(433) 760-3219 </td>
                    <td> Administrator </td>

                    <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a></td>
                </tr>
                <tr class="even">

                    <td>CON2 </td>
                    <td>Patricia</td>
                    <td>Johnson</td>
                    <td>VP Operations </td>
                    <td>t3M Invest A/S</td>
                    <td>patricia_johnson@company.com</td>
                    <td>(463) 950-7751 </td>
                    <td> Administrator </td>
                    <td><a href="javascript:void()">edit</a>  | <a href="javascript:void()">del</a></td>
                </tr>
                <tr class="odd">

                    <td>John</td>
                    <td>Smith</td>
                    <td>Mgr Operations</td>
                    <td>VP Operations</td>
                    <td>t4M Invest A/S</td>
                    <td>john_smith@company.com</td>
                    <td>(433) 160-3243 </td>
                    <td> User </td>

                    <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a></td>
                </tr>

            </table>
            <div id="table_id_info" class="dataTables_info">Showing 1 to 4 of 4 entries
                <span style="margin-left: 800px;" id="table_id_paginate" class="dataTables_paginate paging_two_button" style="margin-left: 620px;">
                    <a aria-controls="table_id" id="table_id_previous" class="paginate_disabled_previous" tabindex="0" role="button">Previous</a>
                    <a aria-controls="table_id" id="table_id_next" class="paginate_disabled_next" tabindex="0" role="button">Next</a>
                </span>
            </div>

        </div>

        <div id="createnew">


        </div>

    </div>
    <div id="createcontact">
        <div style="padding:20px" >

            <span class="lvtHeaderText">Creating New Contact</span> <br>

            <hr size="1" noshade="">
            <br> 



            <form enctype="multipart/form-data" method="POST" name="Addcontact">

                <tr>
                    <td valign="top" align="left">
                        <table style="border:1px solid #589FC8;" cellspacing="0" cellpadding="3" width="100%" border="0" class="dvtContentSpace">
                            <tr>

                                <td align="left">

                                    <table cellspacing="0" cellpadding="0" width="100%" border="0">

                                        <tr>
                                            <td style="padding:10px">
                                                <!-- General details -->
                                                <table cellspacing="0" cellpadding="0" width="100%" border="0" >



                                                    <tr style="height:25px">



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




                                                        <!-- Non Editable field, only configured value will be loaded -->
                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Contact Id </td>
                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="AUTO GEN ON SAVE" id="contact_no" name="contact_no" tabindex="" readonly=""></td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red">*</font>Last Name						</td>

                                                        <td width="30%" align="left" class="dvtCellInfo">
                                                            <input type="text" value="" style="width:58%;" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" tabindex="" name="lastname">
                                                        </td>




                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Office Phone </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="phone" name="phone" tabindex=""></td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>Organization Name 			</td>
                                                        <td width="30%" align="left" class="dvtCellInfo">
                                                            <select style="width:150px" class="txtBox" id="bas_searchfield" name="account_name">
                                                                <option value="firstname" label="Organization1">Organization1</option>
                                                                <option value="lastname" label="Organization2">Organization2</option>
                                                                <option value="Organization3" label="Organization3">Organization3</option>
                                                                <option value="Organization4" label="Organization4">Organization4</option>
                                                                <option value="Organization5" label="Organization5">Organization5</option>
                                                                <option value="Organization6" label="Organization6">Organization6</option>
                                                                <option value="Organization7" label="Organization7">Organization7</option>

                                                            </select>
                                                        </td>



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mobile </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mobile" name="mobile" tabindex=""></td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>
                                                            Lead Source 			</td>
                                                        <td width="30%" align="left" class="dvtCellInfo">
                                                            <select  tabindex="" name="leadsource">
                                                                <option value="--None--">
                                                                    --None--
                                                                </option>
                                                                <option value="Cold Call">
                                                                    Cold Call
                                                                </option>
                                                                <option value="Existing Customer">
                                                                    Existing Customer
                                                                </option>
                                                                <option value="Self Generated">
                                                                    Self Generated
                                                                </option>
                                                                <option value="Employee">
                                                                    Employee
                                                                </option>
                                                                <option value="Partner">
                                                                    Partner
                                                                </option>
                                                                <option value="Public Relations">
                                                                    Public Relations
                                                                </option>
                                                                <option value="Direct Mail">
                                                                    Direct Mail
                                                                </option>
                                                                <option value="Conference">
                                                                    Conference
                                                                </option>
                                                                <option value="Trade Show">
                                                                    Trade Show
                                                                </option>
                                                                <option value="Web Site">
                                                                    Web Site
                                                                </option>
                                                                <option value="Word of mouth">
                                                                    Word of mouth
                                                                </option>
                                                                <option value="Other">
                                                                    Other
                                                                </option>
                                                            </select>
                                                        </td>



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Home Phone </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="homephone" name="homephone" tabindex=""></td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Title </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="title" name="title" tabindex=""></td>



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Other Phone </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="otherphone" name="otherphone" tabindex=""></td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Department </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="department" name="department" tabindex=""></td>



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Fax </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="fax" name="fax" tabindex=""></td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Email </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="email" name="email" tabindex=""></td>



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>Birth date 			</td>
                                                        <td width="30%" align="left" class="dvtCellInfo">

                                                            <input type="text" value="" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_birthday" tabindex="" name="birthday">
                                                            <img id="jscal_trigger_birthday" src="themes/softed/images/btnL3Calendar.gif">




                                                            <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>

                                                            <script id="massedit_calendar_birthday" type="text/javascript">
                                                                Calendar.setup({
                                                                    inputField: "jscal_field_birthday", ifFormat: "%Y-%m-%d", showsTime: false, button: "jscal_trigger_birthday", singleClick: true, step: 1
                                                                })
                                                            </script>


                                                        </td>

                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Assistant </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="assistant" name="assistant" tabindex=""></td>



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>Reports To 			</td>
                                                        <td width="30%" align="left" class="dvtCellInfo">
                                                            <select name="contact_name" id="contact_name">
                                                                <option value="contact1">contact1</option>
                                                                <option value="contact2">contact2</option>
                                                                <option value="contact3">contact3</option>
                                                                <option value="contact4">contact4</option>

                                                            </select>
                                                        </td>

                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Assistant Phone </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="assistantphone" name="assistantphone" tabindex=""></td>



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Secondary Email </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="secondaryemail" name="secondaryemail" tabindex=""></td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>Email Opt Out 			</td>

                                                        <td width="30%" align="left" class="dvtCellInfo">
                                                            <input type="checkbox" tabindex="" name="emailoptout">
                                                        </td>



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>Do Not Call 			</td>

                                                        <td width="30%" align="left" class="dvtCellInfo">
                                                            <input type="checkbox" tabindex="" name="donotcall">
                                                        </td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>Reference 			</td>

                                                        <td width="30%" align="left" class="dvtCellInfo">
                                                            <input type="checkbox" tabindex="" name="reference">
                                                        </td>



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red">*</font>Assigned To 			</td>
                                                        <td width="30%" align="left" class="dvtCellInfo">


                                                            <input type="radio" onclick="toggleAssignType(this.value)" value="U" checked="" name="assigntype" tabindex="">&nbsp;User

                                                            <input type="radio" onclick="toggleAssignType(this.value)" value="T" name="assigntype">&nbsp;Group

                                                            <span style="display:block" id="assign_user">
                                                                <select  name="assigned_user_id">
                                                                    <option selected="" value="1"> Administrator</option>
                                                                </select>
                                                            </span>

                                                            <span style="display:none" id="assign_team">
                                                                <select  name="assigned_group_id">';
                                                                    <option value="3">Marketing Group</option>
                                                                    <option value="4">Support Group</option>
                                                                    <option value="2">Team Selling</option>
                                                                </select>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>Notify Owner 			</td>

                                                        <td width="30%" align="left" class="dvtCellInfo">
                                                            <input type="checkbox" tabindex="" name="notify_owner">
                                                        </td>



                                                    </tr>




                                                    <!-- This is added to display the existing comments -->



                                                    <tr>
                                                        <td class="detailedViewHeader" colspan="4">
                                                            <b>Customer Portal Information</b>
                                                        </td>
                                                    </tr>

                                                    <!-- Handle the ui types display -->


                                                    <!-- Added this file to display the fields in Create Entity page based on ui types  -->
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>Portal User 			</td>

                                                        <td width="30%" align="left" class="dvtCellInfo">
                                                            <input type="hidden" value="" name="existing_portal">
                                                            <input type="checkbox" tabindex="" name="portal">
                                                        </td>



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>Support Start Date 			</td>
                                                        <td width="30%" align="left" class="dvtCellInfo">

                                                            <input type="text" value="2014-01-21" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_support_start_date" tabindex="" name="support_start_date">
                                                            <img id="jscal_trigger_support_start_date" src="themes/softed/images/btnL3Calendar.gif">




                                                            <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>

                                                            <script id="massedit_calendar_support_start_date" type="text/javascript">
                                                                Calendar.setup({
                                                                    inputField: "jscal_field_support_start_date", ifFormat: "%Y-%m-%d", showsTime: false, button: "jscal_trigger_support_start_date", singleClick: true, step: 1
                                                                })
                                                            </script>


                                                        </td>

                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>Support End Date 			</td>
                                                        <td width="30%" align="left" class="dvtCellInfo">

                                                            <input type="text" value="2015-01-21" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_support_end_date" tabindex="" name="support_end_date">
                                                            <img id="jscal_trigger_support_end_date" src="themes/softed/images/btnL3Calendar.gif">




                                                            <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>

                                                            <script id="massedit_calendar_support_end_date" type="text/javascript">
                                                                Calendar.setup({
                                                                    inputField: "jscal_field_support_end_date", ifFormat: "%Y-%m-%d", showsTime: false, button: "jscal_trigger_support_end_date", singleClick: true, step: 1
                                                                })
                                                            </script>


                                                        </td>




                                                    </tr>




                                                    <!-- This is added to display the existing comments -->



                                                    <tr>
                                                        <td class="detailedViewHeader" colspan="2">
                                                            <b>Address Information</b></td>
                                                        <td class="detailedViewHeader">
                                                            <input type="radio" onclick="return copyAddressLeft(EditView)" name="cpy"><b>Copy Other Address</b></td>
                                                        <td class="detailedViewHeader">
                                                            <input type="radio" onclick="return copyAddressRight(EditView)" name="cpy"><b>Copy Mailing Address</b></td>

                                                    </tr>

                                                    <!-- Handle the ui types display -->


                                                    <!-- Added this file to display the fields in Create Entity page based on ui types  -->
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>
                                                            Mailing Street 			</td>
                                                        <td width="30%" align="left" class="dvtCellInfo">
                                                            <textarea rows="2" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" tabindex="" name="mailingstreet" value=""></textarea>
                                                        </td>



                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>
                                                            Other Street 			</td>
                                                        <td width="30%" align="left" class="dvtCellInfo">
                                                            <textarea rows="2" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" tabindex="" name="otherstreet" value=""></textarea>
                                                        </td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mailing PO Box </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mailingpobox" name="mailingpobox" tabindex=""></td>



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Other PO Box </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="otherpobox" name="otherpobox" tabindex=""></td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mailing City </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mailingcity" name="mailingcity" tabindex=""></td>



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Other City </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="othercity" name="othercity" tabindex=""></td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mailing State </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mailingstate" name="mailingstate" tabindex=""></td>



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Other State </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="otherstate" name="otherstate" tabindex=""></td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mailing Postal Code </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mailingzip" name="mailingzip" tabindex=""></td>



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Other Postal Code </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="otherzip" name="otherzip" tabindex=""></td>
                                                    </tr>
                                                    <tr style="height:25px">



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Mailing Country </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="mailingcountry" name="mailingcountry" tabindex=""></td>



                                                        <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Other Country </td>

                                                        <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="othercountry" name="othercountry" tabindex=""></td>
                                                    </tr>




                                                    <!-- This is added to display the existing comments -->



                                                    <tr>
                                                        <td class="detailedViewHeader" colspan="4">
                                                            <b>Description Information</b>
                                                        </td>
                                                    </tr>

                                                    <!-- Handle the ui types display -->


                                                    <!-- Added this file to display the fields in Create Entity page based on ui types  -->
                                                    <tr style="height:25px">



                                                        <!-- In Add Comment are we should not display anything -->
                                                        <td width="20%" align="right" class="dvtCellLabel">
                                                            <font color="red"></font>
                                                            Description 			</td>
                                                        <td colspan="3">
                                                            <textarea rows="8" cols="90" onblur="this.className = 'detailedViewTextBox'" name="description" onfocus="this.className = 'detailedViewTextBoxOn'" tabindex="" class="detailedViewTextBox"></textarea>
                                                        </td>
                                                    </tr>




                                                    <!-- This is added to display the existing comments -->





                                                    <!-- Handle the ui types display -->


                                                    <!-- Added this file to display the fields in Create Entity page based on ui types  -->



                                                    <!-- Added to display the Product Details in Inventory-->

                                                    <tr>
                                                        <td style="padding:5px" colspan="4">
                                                            <div align="center">
                                                                <input type="submit" style="width:70px" value=" Save " name="submit">
                                                                <input type="button" style="width:70px" value="  Cancel  " name="cancel">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                </table>
                <div>
                </div></form></div>

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
