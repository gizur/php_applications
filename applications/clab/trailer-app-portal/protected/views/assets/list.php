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
<div id="content">
    <div class="breadcrumbs">
        <a href="/app/trailer-app-portal/contacts/list">Home</a> » <span><a href="/app/trailer-app-portal/index.php?r=assets/list">Assets List</a></span></div>
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
        <div align="right"><a href="javascript:void(0)" style="cursor: pointer;" onclick="createNew()"><strong>Create New Asset</strong></a></div>
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
                <!-- Table Headers -->
                <tr role="row">

                    <th style="border-bottom: 1px solid #000000;">Asset No</th>
                    <th style="border-bottom: 1px solid #000000;">Asset Name</th>
                    <th style="border-bottom: 1px solid #000000;">Customer Name</th>
                    <th style="border-bottom: 1px solid #000000;">Product Name</th>
                    <th style="border-bottom: 1px solid #000000;">Action</th>
                </tr>
                <!-- Table Contents -->
                <tr class="odd">
                    <td>AST1001 </td>
                    <td>AFN141F</td>
                    <td>Clab</td>
                    <td>lab Trailer </td>
                    <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a></td>
                </tr>

                <tr class="even">
                    <td>AST1002</td>
                    <td>AFU767F</td>
                    <td>Clab</td>
                    <td>lab Trailer </td>
                    <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a></td>
                </tr>

                <tr class="odd">
                    <td>AST1003</td>
                    <td>AGD476F</td>
                    <td>Clab</td>
                    <td>lab Trailer </td>
                    <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a></td>
                </tr>

            </table>
            <div id="table_id_info" class="dataTables_info">Showing 1 to 1 of 1 entries
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
        <div style="padding:20px">

            <span class="lvtHeaderText">Creating New Asset</span> <br>

            <hr size="1" noshade="">
            <br> 



            <form onsubmit="VtigerJS_DialogBox.block();" action="index.php" method="POST" name="EditView">

                <table style="border:1px solid #589FC8;"  cellspacing="0" cellpadding="0" width="95%" border="0" align="center">
                    <tbody>
                        <tr>
                            <td valign="top" align="left">
                                <table cellspacing="0" cellpadding="3" width="100%" border="0" class="dvtContentSpace">
                                    <tbody><tr>

                                            <td align="left">

                                                <table cellspacing="0" cellpadding="0" width="100%" border="0">
                                                    <tbody><tr>
                                                            <td id="autocom"></td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:10px">
                                                                <!-- General details -->
                                                                <table cellspacing="0" cellpadding="0" width="100%" border="0">
                                                                    <tbody>

                                                                        <!-- included to handle the edit fields based on ui types -->



                                                                        <!-- This is added to display the existing comments -->




                                                                        <!-- Added this file to display the fields in Create Entity page based on ui types  -->
                                                                        <tr style="height:25px">



                                                                            <!-- Non Editable field, only configured value will be loaded -->
                                                                            <td width="20%" align="right" class="dvtCellLabel"><font color="red"></font>Asset No </td>
                                                                            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="asset_no" name="asset_no" tabindex="" readonly=""></td>



                                                                            <td width="20%" align="right" class="dvtCellLabel">
                                                                                <font color="red">*</font>
                                                                                Product Name

                                                                                <input type="hidden" value="Products" name="product_type">


                                                                            </td>
                                                                            <td width="30%" align="left" class="dvtCellInfo">
                                                                                <input type="hidden" value="" name="product" id="product">
                                                                                <input type="text" value="" style="border:1px solid #bababa;" readonly="" name="product_display" id="product_display">&nbsp;
                                                                                <img align="absmiddle" style="cursor:hand;cursor:pointer" onclick="return window.open( & quot; index.php?module = & quot; + document.EditView.product_type.value + & quot; & amp; action = Popup & amp; html = Popup_picker & amp; form = vtlibPopupView & amp; forfield = product & amp; srcmodule = Assets & amp; forrecord = & quot; , & quot; test & quot; , & quot; width = 640, height = 602, resizable = 0, scrollbars = 0, top = 150, left = 200 & quot; );" language="javascript" title="Select" alt="Select" tabindex="" src="themes/softed/images/select.gif">&nbsp;
                                                                                <input type="image" align="absmiddle" style="cursor:hand;cursor:pointer" onclick="this.form.product.value = '';
                                                                                        this.form.product_display.value = '';
                                                                                        return false;" language="javascript" title="Clear" alt="Clear" src="themes/images/clear_field.gif">&nbsp;
                                                                            </td>
                                                                        </tr>
                                                                        <tr style="height:25px">



                                                                            <td width="20%" align="right" class="dvtCellLabel">
                                                                                <font color="red">*</font>Serial Number 			</td>
                                                                            <td width="30%" align="left" class="dvtCellInfo">
                                                                                <input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" tabindex="" name="serialnumber">
                                                                            </td>



                                                                            <td width="20%" align="right" class="dvtCellLabel">
                                                                                <font color="red">*</font>Assigned To 			</td>
                                                                            <td width="30%" align="left" class="dvtCellInfo">


                                                                                <input type="radio" onclick="toggleAssignType(this.value)" value="U" checked="" name="assigntype" tabindex="">&nbsp;User

                                                                                <input type="radio" onclick="toggleAssignType(this.value)" value="T" name="assigntype">&nbsp;Group

                                                                                <span style="display:block" id="assign_user">
                                                                                    <select name="assigned_user_id">
                                                                                        <option selected="" value="1"> Administrator</option>
                                                                                    </select>
                                                                                </span>

                                                                                <span style="display:none" id="assign_team">
                                                                                    <select name="assigned_group_id">';
                                                                                        <option value="3">Marketing Group</option>
                                                                                        <option value="4">Support Group</option>
                                                                                        <option value="2">Team Selling</option>
                                                                                    </select>
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                        <tr style="height:25px">



                                                                            <td width="20%" align="right" class="dvtCellLabel">
                                                                                <font color="red">*</font>Date Sold 			</td>
                                                                            <td width="30%" align="left" class="dvtCellInfo">

                                                                                <input type="text" value="" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_datesold" tabindex="" name="datesold">
                                                                                <img id="jscal_trigger_datesold" src="themes/softed/images/btnL3Calendar.gif">




                                                                                <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>

                                                                                <script id="massedit_calendar_datesold" type="text/javascript">
                                                                                    Calendar.setup({
                                                                                        inputField: "jscal_field_datesold", ifFormat: "%Y-%m-%d", showsTime: false, button: "jscal_trigger_datesold", singleClick: true, step: 1
                                                                                    })
                                                                                </script>


                                                                            </td>




                                                                            <td width="20%" align="right" class="dvtCellLabel">
                                                                                <font color="red">*</font>Date in Service 			</td>
                                                                            <td width="30%" align="left" class="dvtCellInfo">

                                                                                <input type="text" value="" maxlength="10" size="11" style="border:1px solid #bababa;" id="jscal_field_dateinservice" tabindex="" name="dateinservice">
                                                                                <img id="jscal_trigger_dateinservice" src="themes/softed/images/btnL3Calendar.gif">




                                                                                <br><font size="1"><em old="(yyyy-mm-dd)">(yyyy-mm-dd)</em></font>

                                                                                <script id="massedit_calendar_dateinservice" type="text/javascript">
                                                                                    Calendar.setup({
                                                                                        inputField: "jscal_field_dateinservice", ifFormat: "%Y-%m-%d", showsTime: false, button: "jscal_trigger_dateinservice", singleClick: true, step: 1
                                                                                    })
                                                                                </script>


                                                                            </td>

                                                                        </tr>
                                                                        <tr style="height:25px">



                                                                            <td width="20%" align="right" class="dvtCellLabel">
                                                                                <font color="red"></font>Tag Number 			</td>
                                                                            <td width="30%" align="left" class="dvtCellInfo">
                                                                                <input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" tabindex="" name="tagnumber">
                                                                            </td>



                                                                        </tr>
                                                                        <tr style="height:25px">



                                                                            <td width="20%" align="right" class="dvtCellLabel">
                                                                                <font color="red"></font>Shipping Method 			</td>
                                                                            <td width="30%" align="left" class="dvtCellInfo">
                                                                                <input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" tabindex="" name="shippingmethod">
                                                                            </td>



                                                                            <td width="20%" align="right" class="dvtCellLabel">
                                                                                <font color="red"></font>Shipping Tracking Number 			</td>
                                                                            <td width="30%" align="left" class="dvtCellInfo">
                                                                                <input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" tabindex="" name="shippingtrackingnumber">
                                                                            </td>
                                                                        </tr>
                                                                        <tr style="height:25px">



                                                                            <td width="20%" align="right" class="dvtCellLabel"><font color="red">*</font>Asset Name </td>

                                                                            <td width="30%" align="left" class="dvtCellInfo"><input type="text" onblur="this.className = 'detailedViewTextBox'" onfocus="this.className = 'detailedViewTextBoxOn'" class="detailedViewTextBox" value="" id="assetname" name="assetname" tabindex=""></td>



                                                                        </tr>




                                                                        <!-- This is added to display the existing comments -->



                                                                        <tr>
                                                                            <td class="detailedViewHeader" colspan="4">
                                                                                <b>Notes</b>
                                                                            </td>
                                                                        </tr>

                                                                        <!-- Handle the ui types display -->


                                                                        <!-- Added this file to display the fields in Create Entity page based on ui types  -->
                                                                        <tr style="height:25px">



                                                                            <!-- In Add Comment are we should not display anything -->
                                                                            <td width="20%" align="right" class="dvtCellLabel">
                                                                                <font color="red"></font>
                                                                                Notes 			</td>
                                                                            <td colspan="3">
                                                                                <textarea rows="8" cols="90" onblur="this.className = 'detailedViewTextBox'" name="description" onfocus="this.className = 'detailedViewTextBoxOn'" tabindex="" class="detailedViewTextBox"></textarea>
                                                                            </td>
                                                                        </tr>



                                                                        <!-- Added to display the Product Details in Inventory-->

                                                                        <tr>
                                                                            <td style="padding:5px" colspan="4">
                                                                                <div align="center">
                                                                                    <input type="submit" style="width:70px" value="  Save  " name="button" onclick="this.form.action.value = 'Save';
                                                                                            displaydeleted();
                                                                                            return formValidate()" class="crmbutton small save" accesskey="S" title="Save [Alt+S]">
                                                                                    <input type="button" style="width:70px" value="  Cancel  " name="button" onclick="window.history.back()" class="crmbutton small cancel" accesskey="X" title="Cancel [Alt+X]">
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody></table>
                                                            </td>
                                                        </tr>
                                                    </tbody></table>
                                            </td>
                                        </tr>
                                    </tbody></table>
                            </td>
                        </tr>
                    </tbody></table>
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
