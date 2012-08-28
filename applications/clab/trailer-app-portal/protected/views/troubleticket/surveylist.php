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
        'Trouble Ticket / Trouble Ticket List',
);
?>
<div style="float:right; margin-bottom:10px; width:140px">
<a href="index.php?r=troubleticket/survey/">Create Trouble Ticket</a></div>
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
 $month = array (1=>"January", "February", "March", "April", "May", "June", "July", "August", "September", "October" ,"November" ,"December");
	foreach($month as $key => $val)
	{
	 $selected=$key==$curr_month ? "selected" : "";	
	 $Months.="<option value=".$key." ".$selected." >".$val."</option>";
	 }
	
	$TrailerIDS = array (1=>"AXT009", "AXT0010", "AXT0011", "AXT0012", "AXT0013", "XYZ010", "XYZ011");
	foreach($TrailerIDS as $key => $val)
	{
		 $TID.="<option value=".$key.">".$val."</option>";
	 }	
		?>
<td><select name='year'><?php echo $options; ?></select></td>
<td><select name='month'><?php echo $Months; ?></select></td>
<td>
<fieldset style="border:1px solid #000">
   
    <span>Trailer</span>&nbsp;&nbsp;&nbsp;&nbsp;
	<select name='TID'><?php echo $TID; ?></select>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="radio" name="optration" value="inperation">&nbsp;&nbsp;In operation
	;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="radio" name="optration" value="damaged">&nbsp;&nbsp;Damaged
	&nbsp;&nbsp;&nbsp;&nbsp;
	</fieldset>
	</td>
</tr>
</table>

</div>
<?php
$columnsArray = array('id','Date.','Time','Account (Tronspoter)','Contact (Driver)','Place',
'Type of Damage','Position on trailer');
$rowsArray = array();
$i=1;
$result['result']=array(1,2,3,4,5);
foreach($result['result'] as $data)
{ 
	
	$date=date('y-m-d',strtotime($data['createdtime']));
	$time=date('h:i',strtotime($data['createdtime']));
	$viewdteails='<a href="index.php?r=troubleticket/surveydetails/'.$data['id'].'">'.$data['account'].'</a>';
	$ticketNo = '<a href="index.php?r=troubleticket/surveydetails/'.$data['id'].'">'.$data['date'].'</a>';
	$rowsArray[] = array($i,$date,$time,$viewdteails,$data['contact'],
	$data['cf_634'],$data['cf_635'],$data['cf_636']);
	$i++;
}

$this->widget('ext.htmltableui.htmlTableUi',array(
    'ajaxUrl'=>'site/handleHtmlTable',
    'arProvider'=>'',    
    'collapsed'=>false,
    'columns'=>$columnsArray,
    'cssFile'=>'',
    'editable'=>false,
    'enableSort'=>true,
    'footer'=>'Total rows: '.count($rowsArray),
    'formTitle'=>'Form Title',
    'rows'=>$rowsArray,
    'sortColumn'=>1,
    'sortOrder'=>'desc',
    'title'=>'List of Trouble Ticket',
));
?>
