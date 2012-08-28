<!-- 
/**
	 * 
	 * 
	 * Created date : 04/07/2012
	 * Created By : Anil Singh
	 * @author Anil Singh <anil-singh@essindia.co.in>
	 * Flow : The basic flow of this page is Create of Trouble tickets (Survey).
	 * Modify date : 05/08/2012
	*/

-->
<?php
$this->pageTitle=Yii::app()->name . ' - New Ticket for Survey ';

echo CHtml::metaTag($content='My page description', $name='decription');

$this->breadcrumbs=array(
        'Trouble Ticket / Survey',
);

?>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'troubleticketsurvey',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	
	),
)); ?>
   <?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>
<div style="background:#E5E5E5"><strong>Create New Survey Ticket</strong></div>	
<div align="center">
<table style="width:100%">
<tr>
 <td>

   <?php echo $form->labelEx($model,'Title'); ?>
   </td><td>
    <?php echo $form->textField($model,'Title'); ?>
    <?php echo $form->error($model,'Title'); ?>
   
  </td>

  <td>
  <?php echo $form->labelEx($model,'Ticket Category'); ?>
  </td><td>
  <?php echo $form->dropDownList($model,'Category', $category); ?>
  <?php echo $form->error($model,'Category'); ?>
  
  </td>
  </tr>
<tr>
 <td>

   <?php echo $form->labelEx($model,'Trailer ID'); ?>&nbsp;<span style="color:red">*</span>
   </td><td>
    <?php echo $form->textField($model,'TrailerID'); ?>
    <?php echo $form->error($model,'TrailerID'); ?>
   
  </td>
  <td>
  <?php echo $form->labelEx($model,'Location for damage report'); ?>&nbsp;<span style="color:red">*</span>
  </td><td>
    <?php echo $form->textField($model,'Damagereportlocation'); ?>
    <?php echo $form->error($model,'Damagereportlocation'); ?>
  
  </td>
  </tr>
<tr>
   <td>
  <?php echo $form->labelEx($model,'Sealed trailer'); ?>
  </td><td>
  <?php echo $form->dropDownList($model,'Sealed',$Sealed);?>
  </td>
   
    <td>
   <?php echo $form->labelEx($model,'Plates'); ?>
  </td><td>
   <?php echo $form->textField($model,'Plates'); ?>
    <?php echo $form->error($model,'Plates'); ?>
    </td> 
   
</tr>


  <tr>
<td>
  <?php echo $form->labelEx($model,'Number of straps'); ?>&nbsp;<span style="color:red">*</span>
  </td><td>
   <?php echo $form->textField($model,'Straps'); ?>
    <?php echo $form->error($model,'Straps'); ?>
  </td>	  
	  
   <td>
  <?php echo $form->labelEx($model,'Type of damage'); ?>
  </td><td>
    <?php echo $form->dropDownList($model,'Typeofdamage',$damagetype);?>
      <?php echo $form->error($model,'Typeofdamage'); ?>
  </td>
 
  </tr>
  <tr>
 <td>
  <?php echo $form->labelEx($model,'Position on trailer for damage'); ?>
  </td><td>
     <?php echo $form->dropDownList($model,'Damageposition',$damagepos);?>
        <?php //echo $form->hiddenField($model,'Damageposition',array('type'=>"hidden",'size'=>2,'maxlength'=>60 ,'value'=>'Höger sida (Right side)')); ?>
    <?php echo $form->error($model,'Damageposition'); ?>
  
  </td>	  
  <td>
  <?php echo $form->labelEx($model,'Upload Pictures'); ?>
  </td>
  <td>
  <?php
    echo $form->fileField($model, 'image');
  ?>
  <?php echo $form->error($model,'image'); ?>

  </td>
  </tr>
  <tr>
<td>
  <?php echo $form->labelEx($model,'Create Date'); ?>
  </td><td>
   <?php echo date('Y-m-d'); ?>
   <?php echo $form->hiddenField($model,'TroubleTicketType',array('type'=>"hidden",'size'=>2,'maxlength'=>2, 'value'=>'survey')); ?>
  </td>
</tr>





</table>
 <?php echo CHtml::submitButton('Save', array('id'=>'submit','name'=>'submit')); ?>

<?php echo CHtml::endForm(); ?>
<?php $this->endWidget(); ?>
