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
include_once 'protected/extensions/language/' . $session['Lang'] . '.php';
?>
<?php
$this->pageTitle = Yii::app()->name . ' - Trouble Ticket List ';
echo CHtml::metaTag($content = 'My page description', $name = 'decription');
$this->breadcrumbs = array(
    getTranslatedString('Trouble ticket') . ' /' . getTranslatedString('Trouble ticket List'),
);
?>
<?php
$currentyear = isset($session['Search_year']) ? $session['Search_year'] : date('Y');
for ($i = 1980; $i <= 2020; $i++) {
    $selected = $i == $currentyear ? "selected" : "";
    $options.= "<option value=" . $i . " " . $selected . " >" . $i . "</option>";
}
$options.= "<option value=0000>All</option>";

$curr_month = isset($session['Search_month']) ? $session['Search_month'] : date("m");
$month = array('00' => "All", '01' => "January", '02' => "February", '03' => "March", '04' => "April", '05' => "May", '06' => "June",
    '07' => "July", '08' => "August", '09' => "September", '10' => "October", '11' => "November", '12' => "December");
foreach ($month as $key => $val) {
    $selected = $key == $curr_month ? "selected" : "";
    $Months.="<option value=" . $key . " " . $selected . " >" . $val . "</option>";
}
$TR = isset($session['Search_trailerid']) ? $session['Search_trailerid'] : 0;
foreach ($Assets as $key => $val) {
    if ($TR == $key)
        $TID .= "<option value=\"" . $key . "\" selected=\"selected\">" . $val . "</option>";
    else
        $TID .= "<option value=\"" . $key . "\">" . $val . "</option>";
}

$rm = array('all' => 'All', 'yes' => 'Damaged');
$rpd = isset($session['Search_reportdamage']) ? $session['Search_reportdamage'] : 'all';
$reportdamage_opt = "";
foreach ($rm as $key => $val) {
    if ($rpd == $key)
        $reportdamage_opt .= "<option value=\"" . $key . "\" selected=\"selected\">" . $val . "</option>";
    else
        $reportdamage_opt .= "<option value=\"" . $key . "\">" . $val . "</option>";
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
<tr><td colspan='4' align="center"><span id='assetsmsg' style="position:fixed; margin:-15px 0 0 350px; "></span></td></tr>
<tr>
                <td valign="top" colspan="4"><table width="100%" border="0" cellspacing="1" cellpadding="1" style="background:#FFF; border:#CCC solid 1px; padding:5px;">
                        <tr>
                            <?php
                            if (!$currentasset) {
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
                            <td><select name='TID' id="trailer" class="search" onchange="getAjaxBaseRecord(this.value)"><?php echo $TID; ?></select></td>
                            <!--<td>
                                 <input type="radio" name="optration" <?php echo $inopt; ?> value="inoperation" id="inperation" onclick="getAjaxBaseAssetRecord(this.value)" value="inperation" style="margin-right:10px"><?php echo getTranslatedString('In operation'); ?>
                                <input type="radio" name="optration" <?php echo $damagechecked; ?> value="damaged" id="damaged" onclick="getAjaxBaseAssetRecord(this.value)" value="damaged" style="margin-right:10px; margin-left:30px"><?php echo getTranslatedString('Damaged'); ?>
                            </td> -->
                        </tr>

                    </table>
                </td>
</tr>
 <tr>
                <td ><select name='year' id="year" ><?php echo $options; ?></select></td>
                <td ><select name='month' id="month" ><?php echo $Months; ?></select></select></td>
                <td >
                    <select name='reportdamage' id="reportdamage" >
                        <?php echo $reportdamage_opt; ?>
                    </select>
                </td>

                            <td><b>Ticket Status</b></td>
                            <td><input type="radio" value="open" id="ticketst" name = "ticketst" checked= 'checked' >Open</td>
                            <td><input type="radio" value="closed" id="ticketst" name="ticketst" >Close</td>
                            <td><input type="radio" value="all" id="ticketst" name="ticketst" >Open/Close</td>
                            <td> <button type="button" class="search" onClick='getAjaxBaseRecord(this.value)'>Search</button> </td>             
                            </tr>

        </table>	
    </div>	
    <div id="alertMsg"></div>
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
                    <th><?php echo getTranslatedString('Damage Reported'); ?></th>
                    <th><?php echo getTranslatedString('Type of damage'); ?></th>
                    <th><?php echo getTranslatedString('Position on trailer'); ?></th>
                    <th><?php echo getTranslatedString('Driver caused damage'); ?></th>
                </tr>
            </thead>
            <tbody>
               <?php foreach ($result['result'] as $data) { ?>
                    <?php $date = date('y-m-d', strtotime(Yii::app()->localtime->toLocalDateTime($data['createdtime']))); ?>
                    <?php $time = date('H:i', strtotime(Yii::app()->localtime->toLocalDateTime($data['createdtime']))); ?>
                    <?php $viewdteails = '<a href="index.php?r=troubleticket/surveydetails/' . $data['id'] . '"  onclick=waitprocess("' . $data['id'] . '")>' . $data['accountname'] . '</a>'; ?>
                    <?php $ticketNo = '<span id=' . $data['id'] . '-1></span><a href="index.php?r=troubleticket/surveydetails/' . $data['id'] . '" onclick=waitprocess("' . $data['id'] . '-1")>' . $data['date'] . '</a>'; ?>
                    <tr>
                        <td><?php echo $data['ticket_no']; ?></td>
                        <td><?php echo $date; ?></td>
                        <td><?php echo $time; ?></td>
                        <td><?php echo $data['trailerid']; ?></td>
                        <td><?php echo $viewdteails; ?></td>
                        <td><?php echo $data['contactname']; ?></td>
                        <td><?php echo htmlentities($data['damagereportlocation'], ENT_QUOTES, "UTF-8"); ?></td>
                        <td><?php echo $data['damagestatus']; ?></td>
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
   var maxdataLimit = 900;
    jQuery(document).ready(function() {
        jQuery("#assetsmsg").show().delay(5000).fadeOut();
        window.dt = jQuery('#table_id').dataTable({
            "bStateSave": true
        });
        window.dt.fnSort( [ [0,'desc'] ] );
         addRows(50,maxdataLimit);
  });
  
  var min=0;
  var allData = [];
  var dataLoad = false;
  function addRows(minLimit, maxLimit) {
       $(".search").attr('disabled','disabled');

      disablelink();
    $("#alertMsg").addClass("waitprocess");
    $('#alertMsg').html('loading....  Please wait');
    var ticketst = $("#ticketst:checked").val();
      dataLoad = $.ajax({url:'index.php?r=troubleticket/surveylistdata',
         type:'POST',
         data: {minLimit:minLimit, maxLimit:maxLimit,ticketstatus:ticketst},
         success:function(data) {
          $.each(data,function(index, value) {
            var fdata = [value.ticket_no,
            value.date,
            value.time,
            value.trailerid,
            '<a href="index.php?r=troubleticket/surveydetails/'+value.id +'"  onclick=waitprocess("'+value.id+'") >' + value.viewdteails + '</a>',
            value.contactname,
            value.damagereportlocation,
            value.damagestatus,
            value.reportdamage,
            value.damagetype,
            value.damageposition,
            value.drivercauseddamage
            ];
        allData.push(fdata);    
    });
window.dt.fnAddData(allData);
window.dt.fnSort( [ [0,'desc'] ] );
allData = [];
    min =min+data.length;
        if(data.length < maxdataLimit) { 
             $(".search").removeAttr('disabled');
            enablelink();
           $("#alertMsg").removeClass("waitprocess");
           $('#alertMsg').html('');
         } else {   
            addRows(min, maxdataLimit);
         }
       },
       error: function(err) {
         if(err.status==0 && err.statusText=='timeout') {
           alert("Error: Request Timeout!");
           window.location.reload();
         }
       },
       timeout:60000,
       dataType:'json'});
    }

     var minS=0;
     var allSearchData = [];
     var searchDataLoad = false;
    function searchData(year, month, trailer, reportdamage, trailerid, minLimit, maxLimit, ticketst) {
         searchDataLoad = $.ajax({url:'index.php?r=troubleticket/surveysearch',
         type:'POST', 
              data:{year: year, month: month, trailer: trailer, 
              reportdamage: reportdamage, trailerid: trailerid, 
              minLimit:minLimit, maxLimit:maxLimit,ticketstatus:ticketst},
              success:  function(data) {
                 if(minS==0) {
                  window.dt.fnClearTable();
                 }
                   $.each(data,function(index, value) {
    var fdata = [value.ticket_no,
                 value.date,
                 value.time,
                 value.trailerid,
                 '<a href="index.php?r=troubleticket/surveydetails/'+value.id +'"  onclick=waitprocess("'+value.id+'")>' + value.viewdteails + '</a>',
                 value.contactname,
                 value.damagereportlocation,
                 value.damagestatus,
                 value.reportdamage,
                 value.damagetype,
                 value.damageposition,
                 value.drivercauseddamage
               ];
    allSearchData.push(fdata);
    });
    window.dt.fnAddData(allSearchData);
    window.dt.fnSort( [ [0,'desc'] ] );
    allSearchData = [];
      minS =minS+data.length;
     if(data.length < maxdataLimit) {
         enablelink();
         $(".search").removeAttr('disabled');

            $("#alertMsg").removeClass("waitprocess");
            $('#alertMsg').html('');
             } else {   
             searchData(year, month, trailer, reportdamage, trailerid, minS, maxdataLimit, ticketst);
              }
       }, 
       error: function(err) {
         if(err.status==0 && err.statusText=='timeout') {
           alert("Error: Request Timeout!");
           window.location.reload();
         }
       },
       timeout:60000,
       dataType:'json'});
   }


    function getAjaxBaseAssetRecord(value) {
        if (value == 'damaged') {
            var tickettype = value;
        } else {
            var tickettype = 'inoperation';
        }
        var trailer = $('#trailer').val();
        $("#assetsmsg").addClass("waitprocess");
        $('#assetsmsg').html('loading....  Please wait');
        $.post('index.php?r=troubleticket/changeassets', {tickettype: tickettype, trailer: trailer},
            function(data) {
                $("#assetsmsg").removeClass("waitprocess");
                $('#assetsmsg').html(data);
            }
        );
    }
    
   function getAjaxBaseRecord(ticketst) {
        disablelink();

       $(".search").attr('disabled','disabled');

       
        if(ticketst == 'closed' || ticketst == 'open') {

} else {   
   ticketst = $("#ticketst:checked").val();
  }

        minS = 0;
        var year = $('#year').val();
        var month = $('#month').val();
        var reportdamage = $('#reportdamage').val();
        var trailer = $('#trailer option:selected').text();
        var trailerid = $('#trailer option:selected').val();
        $("#alertMsg").addClass("waitprocess");
        $('#alertMsg').html('loading....  Please wait');
        window.dt.fnClearTable(); 
        allSearchData = []; 
        if(dataLoad) { dataLoad.abort(); } 
        if(searchDataLoad) { searchDataLoad.abort(); }
        searchData(year, month, trailer, reportdamage, trailerid, 0, maxdataLimit, ticketst);     
    }

    
    function waitprocess(id) {
        $("#" + id).addClass("waitprocessdetails");
        $('#' + id).html('Please wait...');
    }
    function disablelink(){

trouble=document.getElementById('trouble').href;
contact=document.getElementById('contacts').href;
asset=document.getElementById('assets').href;
pass=document.getElementById('password').href;
document.getElementById('trouble').removeAttribute("href");
document.getElementById('contacts').removeAttribute("href");
document.getElementById('assets').removeAttribute("href");
document.getElementById('password').removeAttribute("href");


}

function enablelink(){
document.getElementById('trouble').setAttribute("href",trouble);
document.getElementById('contacts').setAttribute("href",contact);
document.getElementById('assets').setAttribute("href",asset);
document.getElementById('password').setAttribute("href",pass);




}

    /*$('#year').val('<?php if (!empty($SYear)) { echo $SYear; } else { echo date('Y'); } ?>');
    $('#month').val('<?php if (!empty($SMonth)) { echo $SMonth; } else { echo date('m'); } ?>');
    $('#reportdamage').val('<?php if (!empty($SReportdamage)) { echo $SReportdamage; } else { echo 'all'; } ?>');*/
</script>
