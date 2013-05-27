<!-- 
/**
         * 
         * 
         * Created date : 04/07/2012
         * Created By : Anil Singh
         * @author Anil Singh <anil-singh@essindia.co.in>
         * Flow : The basic flow of this page is List of Trouble tickets (Survey).
         * Modify date : 13/08/2012
        */

-->
<?php
include_once 'protected/extensions/language/' . Yii::app()->session['Lang'] . '.php';
?>
<?php
$this->pageTitle = Yii::app()->name . ' - Trouble Ticket List ';
echo CHtml::metaTag($content = 'My page description', $name = 'decription');
$this->breadcrumbs = array(
    getTranslatedString('Trouble ticket') . ' /' . getTranslatedString('Trouble ticket List'),
);
?>
<?php
for ($i = 1980; $i <= 2020; $i++) {
    $currentyear = date('Y');
    $selected = $i == $currentyear ? "selected" : "";
    $options.= "<option value=" . $i . " " . $selected . " >" . $i . "</option>";
}
$options.= "<option value=0000>All</option>";

$curr_month = date("m");
$month = array('00' => "All", '01' => "January", '02' => "February", '03' => "March", '04' => "April", '05' => "May", '06' => "June",
    '07' => "July", '08' => "August", '09' => "September", '10' => "October", '11' => "November", '12' => "December");
foreach ($month as $key => $val) {
    $selected = $key == $curr_month ? "selected" : "";
    $Months.="<option value=" . $key . " " . $selected . " >" . $val . "</option>";
}

foreach ($Assets as $key => $val) {
    if ($TR == $val)
        $TID.="<option value=\"" . $key . "\" selected=\"selected\">" . $val . "</option>";
    else
        $TID.="<option value=\"" . $key . "\">" . $val . "</option>";
}
?>
<div id="wrap">
    <?php if (Yii::app()->params['createTroubleTicket']) { ?>
        <div style="float:right; width:208px" class="button">
            <a href="index.php?r=troubleticket/survey/"><?php echo getTranslatedString('Create new Trouble ticket'); ?></a>
        </div>
    <?php } ?>    
    <div class="toppanel">
        <table width="100%" border="0" cellspacing="0" cellpadding="1">
            <tr><td colspan='4' align="center"><span id='assetsmsg' style="position:fixed; margin:-15px 0 0 350px; "></span></td></tr>
            <tr>
                <td ><select name='year' id="year" onchange="getAjaxBaseRecord(this.value)"><?php echo $options; ?></select></td>
                <td ><select name='month' id="month" onchange="getAjaxBaseRecord(this.value)"><?php echo $Months; ?></select></select></td>
                <td >
                    <select name='reportdamage' id="reportdamage" onchange="getAjaxBaseRecord(this.value)">
                        <option value="all">All</option>
                        <option value="yes">Damaged</option>
                    </select>
                </td>
                <td valign="top"><table width="100%" border="0" cellspacing="1" cellpadding="1" style="background:#FFF; border:#CCC solid 1px; padding:5px;">
                        <tr>
                            <?php
                            if (!isset($currentasset)) {
                                $inopt = "disabled=disabled";
                                $damagechecked = "disabled=disabled";
                            } else {
                                if ($currentasset['result']['assetstatus'] == 'Out-of-service') {
                                    $damagechecked = "checked=checked";
                                    $inopt = "";
                                } else {
                                    $inopt = "checked=checked";
                                    $damagechecked = "";
                                }
                            }
                            ?>
                            <td><strong>Trailer</strong></td>
                            <td><select name='TID' id="trailer" onchange="getAjaxBaseRecord(this.value)"><?php echo $TID; ?></select></td>
                            <td><input type="radio" name="optration" <?php echo $inopt; ?> value="inoperation" id="inperation" onclick="getAjaxBaseAssetRecord(this.value)" value="inperation" style="margin-right:10px"><?php echo getTranslatedString('In operation'); ?>	
                                <input type="radio" name="optration" <?php echo $damagechecked; ?> value="damaged" id="damaged" onclick="getAjaxBaseAssetRecord(this.value)" value="damaged" style="margin-right:10px; margin-left:30px"><?php echo getTranslatedString('Damaged'); ?>	</td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>	
    </div>	
    <br />
    <div id="process">
        <table id="table_id">
            <thead>
                <tr>
                    <th><?php echo getTranslatedString('ID'); ?></th>
                    <th><?php echo getTranslatedString('Date'); ?></th>
                    <th><?php echo getTranslatedString('Time'); ?></th>
                    <th><?php echo getTranslatedString('Trailer ID'); ?></th>
                    <th><?php echo getTranslatedString('Account'); ?></th>
                    <th><?php echo getTranslatedString('Contact'); ?></th>
                    <th><?php echo getTranslatedString('Place'); ?></th>
                    <th><?php echo getTranslatedString('Damage Status'); ?></th>
                    <th><?php echo getTranslatedString('Anteckningar'); ?></th>
                    <th><?php echo getTranslatedString('Damage Reported'); ?></th>
                    <th><?php echo getTranslatedString('Type of damage'); ?></th>
                    <th><?php echo getTranslatedString('Position on trailer'); ?></th>
                    <th><?php echo getTranslatedString('Driver caused damage'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['result'] as $data) { ?>
                    <?php $date = date('y-m-d', strtotime($data['createdtime'])); ?>
                    <?php $time = date('h:i', strtotime($data['createdtime'])); ?>
                    <?php $viewdteails = '<span id=' . $data['id'] . '></span><a href="index.php?r=troubleticket/surveydetails/' . $data['id'] . '" onclick=waitprocess("' . $data['id'] . '")>' . $data['accountname'] . '</a>'; ?>
                    <?php $ticketNo = '<span id=' . $data['id'] . '-1></span><a href="index.php?r=troubleticket/surveydetails/' . $data['id'] . '" onclick=waitprocess("' . $data['id'] . '-1")>' . $data['date'] . '</a>'; ?>
                    <tr>
                        <td><?php echo $data['ticket_no']; ?></td>
                        <td><?php echo $date; ?></td>
                        <td><?php echo $time; ?></td>
                        <td><?php echo $data['trailerid']; ?></td>
                        <td><?php echo $viewdteails; ?></td>
                        <td><?php echo $data['contactname']; ?></td>
                        <td><?php echo htmlentities($data['damagereportlocation'], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo htmlentities($data['notes'], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo getTranslatedString(htmlentities($data['damagestatus'], ENT_QUOTES, "UTF-8")); ?></td>
                        <td><?php echo $data['reportdamage']; ?></td>
                        <td><?php echo htmlentities($data['damagetype'], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo htmlentities($data['damageposition'], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo $data['drivercauseddamage']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
                    jQuery(document).ready(function() {
                        jQuery("#assetsmsg").show().delay(5000).fadeOut();
                        jQuery('#table_id').dataTable();
                    });
                    function getAjaxBaseAssetRecord(value)
                    {

                        if (value == 'damaged')
                        {
                            var tickettype = value;
                        }
                        else
                        {
                            var tickettype = 'inoperation';
                        }
                        var trailer = $('#trailer').val();
                        $("#assetsmsg").addClass("waitprocess");
                        $('#assetsmsg').html('loading....  Please wait');
                        $.post('index.php?r=troubleticket/changeassets', {tickettype: tickettype, trailer: trailer},
                        function(data)
                        {
                            $("#assetsmsg").removeClass("waitprocess");
                            $('#assetsmsg').html(data);
                        });

                    }
                    function getAjaxBaseRecord(value)
                    {
                        var year = $('#year').val();
                        var month = $('#month').val();
                        var reportdamage = $('#reportdamage').val();
                        var trailer = $('#trailer option:selected').text();
                        $("#process").addClass("waitprocess");
                        $('#process').html('loading....  Please wait');
                        $.post('index.php?r=troubleticket/surveysearch', {year: year, month: month, trailer: trailer, reportdamage: reportdamage},
                        function(data)
                        {
                            $("#process").removeClass("waitprocess");
                            $('#wrap').html(data);
                        });

                    }


                    function waitprocess(id)
                    {
                        $("#" + id).addClass("waitprocessdetails");
                        $('#' + id).html('Please wait...');
                    }
                    $('#year').val('<?php
                if (!empty($SYear)) {
                    echo $SYear;
                } else {
                    echo date('Y');
                }
                ?>');
                    $('#month').val('<?php
                if (!empty($SMonth)) {
                    echo $SMonth;
                } else {
                    echo date('m');
                }
                ?>');
                    $('#reportdamage').val('<?php
                if (!empty($SReportdamage)) {
                    echo $SReportdamage;
                } else {
                    echo 'all';
                }
                ?>');
</script>
