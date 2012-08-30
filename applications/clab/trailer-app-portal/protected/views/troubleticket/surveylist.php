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
$this->pageTitle=Yii::app()->name . ' - Trouble Ticket List ';

echo CHtml::metaTag($content='My page description', $name='decription');

$this->breadcrumbs=array(
        getTranslatedString('Trouble ticket').' /'. getTranslatedString('Trouble ticket List'),
);
?>
<div style="float:right; margin-bottom:10px; width:140px">
<a href="index.php?r=troubleticket/survey/"><?php echo getTranslatedString('Create new Trouble ticket');?></a></div>
<div style="float:left;width:740px">
<table>
<tr>
	<?php 
	for($i=1980;$i<=2020;$i++)
	{
		$currentyear=date('Y'); 
		$selected = $i==$currentyear ? "selected" : "";
		$options.= "<option value=".$i." ".$selected." >".$i."</option>"; 
		} 
		
	
 $curr_month = date("m"); 
 $month = array ('01'=>"January", '02' => "February",  '03' => "March",  '04' => "April",  '05' => "May",  '06' => "June", 
                 '07' =>  "July", '08' => "August",    '09' => "September", '10' => "October" , '11' => "November" , '12' => "December");
	foreach($month as $key => $val)
	{
	 $selected=$key==$curr_month ? "selected" : "";	
	 $Months.="<option value=".$key." ".$selected." >".$val."</option>";
	 }
	
	$TrailerIDS = array ('0' => "AXT009", '1' => "AXT0010", '2' => "AXT0011", '3' =>  "AXT0012", '4' => "AXT0013",'5' => "XYZ010", '6' => "XYZ011");
	foreach($TrailerIDS as $key => $val)
	{
		 $TID.="<option value=".$key.">".$val."</option>";
	 }	
		?>
<td><select name='year' id="year" onchange="getAjaxBaseRecord(this.value)"><?php echo $options; ?></select></td>
<td><select name='month' id="month" onchange="getAjaxBaseRecord(this.value)"><?php echo $Months; ?></select></td>
<td>
<fieldset style="border:1px solid #000">
   
    <span>Trailer</span>&nbsp;&nbsp;&nbsp;&nbsp;
	<select name='TID' id="trailer" onchange="getYearBaseRecord(this)"><?php echo $TID; ?></select>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="radio" name="optration" checked="checked" value="inoperation" id="inperation" onclick="getAjaxBaseRecord(this.value)" value="inperation">&nbsp;&nbsp;<?php echo getTranslatedString('In operation'); ?>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="radio" name="optration" value="damaged" id="damaged" onclick="getAjaxBaseRecord(this.value)" value="damaged">&nbsp;&nbsp;<?php echo getTranslatedString('Damaged'); ?>
	&nbsp;&nbsp;&nbsp;&nbsp;
	</fieldset>
	</td>
</tr>
</table>
</div>
<br />
<br />
<br />
<div id="process">
<?php
$columnsArray = array(getTranslatedString('ID'),getTranslatedString('Date'),getTranslatedString('Time'),getTranslatedString('Account'),getTranslatedString('Contact'),getTranslatedString('Place'), getTranslatedString('Damage Reported'),
getTranslatedString('Type of damage'),getTranslatedString('Position on trailer'));
$rowsArray = array();
$i=1;
//$result['result']=array(1,2,3,4,5);
foreach($result['result'] as $data)
{ 
	
	$date=date('y-m-d',strtotime($data['createdtime']));
	$time=date('h:i',strtotime($data['createdtime']));
	$viewdteails='<a href="index.php?r=troubleticket/surveydetails/'.$data['id'].'">'.Yii::app()->session['account'].'</a>';
	$ticketNo = '<a href="index.php?r=troubleticket/surveydetails/'.$data['id'].'">'.$data['date'].'</a>';
	$rowsArray[] = array($i,$date,$time,$viewdteails,Yii::app()->session['contactname'],
	$data['damagereportlocation'],$data['reportdamage'],$data['damagetype'],$data['damageposition']);
	$i++;
}
$this->widget('ext.htmltableui.htmlTableUi',array(
    'ajaxUrl'=>'site/handleHtmlTable',
    'arProvider'=>'', 
    'enablePager'=>true,   
    'collapsed'=>false,
    'columns'=>$columnsArray,
    'cssFile'=>'',
    'editable'=>false,
    'enableSort'=>true,
    'footer'=> getTranslatedString('Total rows').': '.count($rowsArray),
    'formTitle'=>'Form Title',
    'rows'=>$rowsArray,
    'sortColumn'=>1,
    'sortOrder'=>'desc',
    'title'=> getTranslatedString('Trouble ticket List'),
));
?>
</div>
<script>
function getAjaxBaseRecord(value)
{
if(value=='damaged')
{
 var tickettype=value;
 }	
else
{
 var tickettype='inoperation';
 }
var year=$('#year').val();
var month=$('#month').val();
var trailer=$('#trailer').val();
$("#process").addClass("waitprocess");	
$('#process').html('loading....  Please wait');
$.post('index.php?r=troubleticket/surveysearch',{tickettype: tickettype, year: year, month: month ,trailer:trailer},
 function(data) 
{
$("#process").removeClass("waitprocess");
$('#process').html(data);
});
	
	
}

</script>
