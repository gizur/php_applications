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
                        <td><strong>First Name : </strong></td><td><input size="15pt" type="text" name="firstname" value="" /></td>
                        <td><strong>Last Name : </strong></td><td><input size="15pt" type="text" name="lastname" value="" /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><strong>Email : </strong></td><td><input size="15pt" type="text" name="email" value="" /></td>
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
                        
                    </tr>
                </table>
            </td>
            </tr>
        </table>
    </div>
    <div align="right"><a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php?r=contacts/add" style="cursor: pointer;"><strong>Create New Contact</strong></a></div>
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

                <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a>  | <a href='javascript:void()'>Reset Password</a></td>
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
                <td><a href="javascript:void()">edit</a>  | <a href="javascript:void()">del</a>  | <a href='javascript:void()'>Reset Password</a></td>
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

                <td><a href="#">edit</a>  | <a href='javascript:void()'>del</a>  | <a href='javascript:void()'>Reset Password</a></td>
            </tr>

        </table>
        <div width="100%" id="table_id_info" class="dataTables_info">Showing 1 to 3 of 3 entries
            <span style="float:right;" id="table_id_paginate" class="dataTables_paginate paging_two_button" style="margin-left: 620px;">
                <a aria-controls="table_id" id="table_id_previous" class="paginate_disabled_previous" tabindex="0" role="button">Previous</a>
                <a aria-controls="table_id" id="table_id_next" class="paginate_disabled_next" tabindex="0" role="button">Next</a>
            </span>
        </div>

    </div>

</div>
