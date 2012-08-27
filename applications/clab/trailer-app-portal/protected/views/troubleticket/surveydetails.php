<!-- 
/**
	 * 
	 * 
	 * Created date : 04/07/2012
	 * Created By : Anil Singh
	 * @author Anil Singh <anil-singh@essindia.co.in>
	 * Flow : The basic flow of this page is Details of Trouble tickets.
	 * Modify date : 13/08/2012
	*/

-->
<?php
$this->pageTitle=Yii::app()->name . ' - New Ticket for Survey ';

echo CHtml::metaTag($content='My page description', $name='decription');

$this->breadcrumbs=array(
        'Trouble Ticket / Survey Details',
);
?>
<div style="float:right; margin-bottom:10px">
<a href="index.php?r=troubleticket/surveylist/">List of Survey Ticket</a></div>

<div style="background:#E5E5E5"><strong>Ticket Information : <?php echo $result['result'][0]['ticket_title']; ?></strong></div>	
<div align="center">
<table style="width:100%">
<tr>
	<td> <strong>Ticket Id</strong>  </td>
   <td>  <?php echo $result['result'][0]['ticket_no']; ?> </td>
</tr>

<tr>
  <td>  <strong>Status</strong> </td>
   <td>  <?php echo $result['result'][0]['ticketstatus']; ?> </td>
</tr>	
	
<tr>
   <td>
    <strong>Title</strong>
   </td>
   <td>
    <?php echo $result['result'][0]['ticket_title']; ?>
   </td>
   
  </tr>
  
<tr>
  <td>
   <strong>Ticket Category</strong>
  </td><td>
     <?php echo $result['result'][0]['ticketcategories']; ?>
  
  </td>
  </tr>
 <tr>
 <td>
    <strong>Trailer ID</strong>
   </td><td>
    <?php echo $result['result'][0]['cf_628']; ?>
   
   </td>
</tr>
   <tr>
  <td>
 <strong> Location for damage report</strong>
  </td><td>
    <?php echo $result['result'][0]['cf_634']; ?>
  
  </td>
  </tr>
    
  <tr>
   <td>
   <strong>Sealed trailer</strong>
  </td><td>
  <?php echo $result['result'][0]['cf_637']; ?>
  </td>
    </tr>
     <tr> 
    <td>
    <strong>Plates</strong>
  </td><td>
  <?php echo $result['result'][0]['cf_631']; ?>
  </td> 
    
</tr>

<tr>
   <td>
   <strong>Number of straps</strong>
  </td><td>
   <?php echo $result['result'][0]['cf_632']; ?>
  </td>
    </tr>
   <tr>
  
  <td>
   <strong>Create Date </strong>
  </td><td>
  <?php echo $result['result'][0]['createdtime']; ?>
  </td>
</tr>

</table>
