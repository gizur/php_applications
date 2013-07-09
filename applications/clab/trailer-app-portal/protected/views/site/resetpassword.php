<?php
$this->pageTitle=Yii::app()->name . ' - Login';
$this->breadcrumbs=array(
	'Reset Password',
);
?>
<center>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'reset-form',
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
	<div class="row">
		<?php echo $form->labelEx($model,'username'); ?>
		<?php echo $form->textField($model,'username'); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

	
	<div class="row buttons">
		<?php echo CHtml::submitButton('Reset Password', array('id'=>'submit','name'=>'submit')); ?> &nbsp;&nbsp;<a href="<?php echo Yii::app()->request->baseUrl; ?>/index.php" id="resetp"> << Back >>
</a>	</div>  

<?php $this->endWidget(); ?>
</div><!-- form -->
