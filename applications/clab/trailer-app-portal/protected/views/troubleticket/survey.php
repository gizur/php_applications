<!-- 
 * vtigerCRM YiiPortal - web based vtiger CRM Customer Portal
 * Copyright (C) 2012 Gizur.
 *
 * This file is part of YiiPortal.
 * Create a new trouble tickets (Survey)
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

   <?php echo $form->labelEx($model,'Trailer ID'); ?>
   </td><td>
    <?php echo $form->textField($model,'TrailerID'); ?>
    <?php echo $form->error($model,'TrailerID'); ?>
   
  </td>
  <td>
  <?php echo $form->labelEx($model,'Location for damage report'); ?>
  </td><td>
    <?php echo $form->textField($model,'Damagereport'); ?>
    <?php echo $form->error($model,'Damagereport'); ?>
  
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
  <?php echo $form->labelEx($model,'Number of straps'); ?>
  </td><td>
   <?php echo $form->textField($model,'Straps'); ?>
    <?php echo $form->error($model,'Straps'); ?>
  </td>
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
