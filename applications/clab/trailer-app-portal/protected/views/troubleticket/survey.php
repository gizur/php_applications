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
include_once 'protected/extensions/language/' . Yii::app()->session['Lang'] . '.php';
?>
<?php
$this->pageTitle = Yii::app()->name . ' - New Ticket for Survey ';

echo CHtml::metaTag($content = 'My page description', $name = 'decription');

$this->breadcrumbs = array(
    getTranslatedString('Trouble ticket') . ' /' . getTranslatedString('Create new Trouble ticket'),
);
?>
<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'troubleticketsurvey',
    'htmlOptions' => array('enctype' => 'multipart/form-data'),
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
    ));
?>
<?php
foreach (Yii::app()->user->getFlashes() as $key => $message) {
    echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
}
?>
<div style="background:#E5E5E5"><strong><?php echo getTranslatedString('Create new Trouble ticket'); ?></strong></div>	
<div align="center">
    <table style="width:100%">
        <tr>
            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Title')); ?>
            </td><td>
                <?php echo $form->textField($model, 'Title', array('value' => $postdata['Title'])); ?>
                <?php echo $form->error($model, 'Title'); ?>

            </td>

            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Ticket Category')); ?>
            </td><td>
                <?php echo $form->dropDownList($model, 'Category', $category); ?>
                <?php echo $form->error($model, 'Category'); ?>

            </td>
        </tr>
        <tr>
            <td>

                <?php echo $form->labelEx($model, getTranslatedString('Trailer ID')); ?>&nbsp;<span style="color:red">*</span>
            </td><td>
                <?php echo $form->dropDownList($model, 'TrailerID', $Assets); ?>
                <?php echo $form->error($model, 'TrailerID'); ?>

            </td>
            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Location for damage report')); ?>&nbsp;<span style="color:red">*</span>
            </td><td>
                <?php echo $form->textField($model, 'Damagereportlocation', array('value' => $postdata['Damagereportlocation'])); ?>
                <?php echo $form->error($model, 'Damagereportlocation'); ?>

            </td>
        </tr>
        <tr>
            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Sealed')); ?>
            </td><td>
                <?php echo $form->dropDownList($model, 'Sealed', $Sealed); ?>
            </td>
            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Plates')); ?>
            </td><td>
                <?php echo $form->textField($model, 'Plates', array('value' => $postdata['Plates'])); ?>
                <?php echo $form->error($model, 'Plates'); ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Straps')); ?>&nbsp;<span style="color:red">*</span>
            </td><td>
                <?php echo $form->textField($model, 'Straps', array('value' => $postdata['Straps'])); ?>
                <?php echo $form->error($model, 'Straps'); ?>
            </td>	  

            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Type of damage')); ?>
            </td><td>
                <?php echo $form->dropDownList($model, 'Typeofdamage', $damagetype); ?>
                <?php echo $form->error($model, 'Typeofdamage'); ?>
            </td>

        </tr>
        <tr>
            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Position on trailer for damage')); ?>
            </td><td>
                <?php echo $form->dropDownList($model, 'Damageposition', $damagepos); ?>
                <?php //echo $form->hiddenField($model,'Damageposition',array('type'=>"hidden",'size'=>2,'maxlength'=>60 ,'value'=>'Höger sida (Right side)')); ?>
                <?php echo $form->error($model, 'Damageposition'); ?>

            </td>	  
            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Driver caused damage')); ?>
            </td><td>

                <?php echo $form->dropDownList($model, 'drivercauseddamage', $drivercauseddamageList); ?>
            </td>	
        </tr>
        <tr>
            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Damage Reported')); ?>
            </td>
            <td>
                <?php echo $form->dropDownList($model, 'reportdamage', $reportdamage); ?>
            </td>
            <td><?php echo $form->labelEx($model, getTranslatedString('Ticket Status')); ?></td>
            <td><?php
                $ticketstatus2 = array();
                foreach ($ticketstatus as $status) {

                    if ($status == 'Open' || $status == 'Closed') {
                        $ticketstatus2[$status] = $status;
                    }
                }
                echo $form->dropDownList($model, 'ticketstatus', $ticketstatus2);
                ?></td>
        </tr>
        <tr>
            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Upload Pictures')); ?>
            </td>
            <td>
                <?php
                echo $form->fileField($model, 'image');
                ?>
                <?php echo $form->error($model, 'image'); ?>
                <span id="addmorepic"><img src="<?php echo Yii::app()->request->baseUrl; ?>/images/001_01.png" title="Add More Pictures" /></span>
                <p id="morepic"></p> 
            </td>
            <td>
                <?php echo $form->labelEx($model, getTranslatedString('Create Date')); ?>
            </td>
            <td>
                <?php echo date('Y-m-d'); ?>
                <?php echo $form->hiddenField($model, 'TroubleTicketType', array('type' => "hidden", 'size' => 2, 'maxlength' => 2, 'value' => 'survey')); ?>
            </td>
        </tr>
    </table>
    <?php echo CHtml::submitButton(getTranslatedString('Submit'), array('id' => 'submit', 'name' => 'submit')); ?>
    <?php echo CHtml::endForm(); ?>
    <?php $this->endWidget(); ?>

    <script>
        var data = "<div><input type='file' name='Troubleticket[image";
        var data2 = "]' id='file";
        var data3 = "' />&nbsp;<span style='cursor:pointer'><img src='<?php echo Yii::app()->request->baseUrl; ?>/images/buttons_21.png' title='Remove Picurest'  onclick='removefiles(this)'/></span></div>";
        $("#addmorepic").click(function() {
            var counter = $('input[type=file]').length;
            $('#morepic').append(data + counter + data2 + counter + data3);
        });

        function removefiles(obj)
        {
            $(obj).parents("div:first").remove();

        }

    </script>

