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
$columnsArray = array(getTranslatedString('ID'),getTranslatedString('Date'),getTranslatedString('Time'),getTranslatedString('Account'),getTranslatedString('Contact'),getTranslatedString('Place'), getTranslatedString('Damage Reported'),
getTranslatedString('Type of damage'),getTranslatedString('Position on trailer'));
$rowsArray = array();
$i=1;
//$result['result']=array(1,2,3,4,5);
foreach($result['result'] as $data)
{ 
	
	$date=date('y-m-d',strtotime($data['createdtime']));
	$time=date('h:i',strtotime($data['createdtime']));
	$viewdteails='<a href="index.php?r=troubleticket/surveydetails/'.$data['id'].'">Exceed99</a>';
	$ticketNo = '<a href="index.php?r=troubleticket/surveydetails/'.$data['id'].'">'.$data['date'].'</a>';
	$rowsArray[] = array($i,$date,$time,$viewdteails,'Markus jane',
	$data['damagereportlocation'],'Yes',$data['damagetype'],$data['damageposition']);
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
