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
include_once 'protected/extensions/language/' . Yii::app()->session['Lang'] . '.php';
?>
<?php
$baseUrl = Yii::app()->baseUrl;
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile($baseUrl . '/assets/a719a609/jquery.js');
?>
<?php
$this->pageTitle = Yii::app()->name . ' - New Ticket for Survey ';

echo CHtml::metaTag($content = 'My page description', $name = 'decription');

$this->breadcrumbs = array(
    getTranslatedString('Trouble Ticket') . '/' . getTranslatedString('Survey Details'),
);
?>
<div style="float:right; margin-bottom:10px" class="button">
    <a href="index.php?r=troubleticket/surveylist/"><?php echo getTranslatedString('Trouble ticket List'); ?></a></div>

<div style="background:#E5E5E5; width:550px"><strong>Ticket Information : <?php echo $result['result']['ticket_title']; ?></strong></div>	

<div class="Survey">
    <h2><?php echo getTranslatedString('Survey'); ?></h2>
    <table width="100%" border="0" bgcolor="#589fc8" cellspacing="1" cellpadding="5">
        <tr>
            <td width="26%" bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Ticket ID'); ?></strong></td>
            <td width="74%" bgcolo3f0f7"> <?php echo $result['result']['ticket_no']; ?></td>
        </tr>
        <tr>
            <td bgcolor="e"><strong><?php echo getTranslatedString('Trailer ID'); ?></strong></td>
            <td bgcolor="e3f0f7"> <?php echo $result['result']['trailerid']; ?></td>
        </tr>
        <tr>
            <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Date'); ?></strong></td>
            <td bgcolor="e3f0f7"><?php echo date('Y-m-d', strtotime(Yii::app()->localtime->toLocalDateTime($result['result']['createdtime']))); ?></td>
        </tr>
        <tr>
            <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Time'); ?></strong></td>
            <td bgcolor="e3f0f7"><?php echo date('H:i', strtotime(Yii::app()->localtime->toLocalDateTime($result['result']['createdtime']))); ?></td>
        </tr>
        <tr>
            <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Place'); ?> </strong></td>
            <td bgcolor="e3f0f7"><?php echo $result['result']['damagereportlocation']; ?></td>
        </tr> 
        <tr>
            <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Account'); ?></strong></td>
            <td bgcolor="e3f0f7"><?php echo $result['result']['accountname']; ?></td>
        </tr>
        <tr>
            <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Contact'); ?></strong></td>
             <td bgcolor="e3f0f7"><?php echo $result['result']['contactname']; ?></td>

        </tr>

        </tr>
    </table>
</div>
<div class="Damage">

    <h2><?php echo getTranslatedString('Damage'); ?></h2>

    <table width="100%" border="0" cellspacing="5" style="border:#589fc8 solid 1px; padding:5px;" cellpadding="0">
        <tr>
            <td width="50%" valign="top"><table width="100%" border="0" bgcolor="#589fc8" cellspacing="1" cellpadding="5">
                    <tr>
                        <td width="50%" bgcolor="7eb6d5"><?php echo getTranslatedString('Type of damage'); ?></td>
                        <td width="50%" bgcolor="7eb6d5">  <?php echo $result['result']['damagetype']; ?></td>
                    </tr>
                    <tr>
                        <td bgcolor="e3f0f7"><?php echo getTranslatedString('Position on trailer'); ?></td>
                        <td bgcolor="e3f0f7"><?php echo $result['result']['damageposition']; ?></td>
                    </tr>
                    <tr>
                        <td bgcolor="e3f0f7"><?php echo getTranslatedString('Status of damage'); ?></td>
                        <td bgcolor="e3f0f7" id="markdamagebutton"><?php echo $result['result']['ticketstatus']; ?></td>
                    </tr>

                </table>

                <br>
                <input type="button"  onclick="AjaxMarkDamage('17x<?php echo $result['result']['id']; ?>')" class="button" value="<?php echo getTranslatedString('Mark damage repaired'); ?>" />


            </td>
            <td width="15%" valign="top">
                <table width="100%" border="0" bgcolor="#589fc8" cellspacing="1" cellpadding="5">
                    <tr>
                        <td colspan="2" bgcolor="7eb6d5"  valign="top"><strong><?php echo getTranslatedString('Pictures'); ?></strong></td>
                    </tr>
                    <tr>
                        <?php
                        $i = 1;
                        if (count($result['result']['documents']) > 0) {
                            foreach ($result['result']['documents'] as $image) {
                                echo '<td width="50%" bgcolor="e3f0f7" align="center">' .
                                    '<img src="' . Yii::app()->request->baseUrl . '/index.php?r=troubleticket/images/' . $image['id'] . 
                                    '" width="100px" height="100px" style="cursor: pointer;" onclick="openwindow(this.src);">' .
                                    '</td>';
                                if ($i % 2 == 0) {
                                    echo "</tr><tr>";
                                }
                                $i++;
                            }
                        }
                        ?> 
                    </tr>
                </table>                
            </td>
        </tr>
    </table>

    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'updatedamagestatusandnotes',
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
    <h2><?php echo getTranslatedString('Damage Status'); ?></h2>
    <table width="100%" border="0" cellspacing="5" style="border:#589fc8 solid 1px; padding:5px;" cellpadding="0">
        <tr>
            <td width="50%" valign="top"><div id="damage_status"></div></td>
        </tr>
        <tr>
            <td width="50%" valign="top">
                <table width="100%" border="0" bgcolor="#589fc8" cellspacing="1" cellpadding="5">                                
                    <tr>
                        <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Damage Status'); ?> </strong></td>
                        <td bgcolor="e3f0f7">
                            <?php
                            echo $form->hiddenField($model, 'id', array('type' => "hidden", 'value' => $result['result']['id']));
                            foreach($damagestatus as $k => $v):
                                unset($damagestatus[$k]);
                                $damagestatus[html_entity_decode($k, ENT_QUOTES, "UTF-8")] = getTranslatedString(html_entity_decode($v, ENT_QUOTES, "UTF-8"));
                            endforeach;
                            echo $form->dropDownList($model, 'damagestatus', $damagestatus, array('prompt' => 'Select', 'encode' => false, 'options' => array($result['result']['damagestatus'] => array('selected' => true))));
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="e3f0f7"><strong><?php echo getTranslatedString('Anteckningar'); ?> </strong></td>
                        <td bgcolor="e3f0f7"><?php $model->notes = $result['result']['notes']; echo $form->textArea($model, 'notes', array('maxlength' => 300, 'rows' => 5, 'cols' => 80)); ?></td>
                    </tr> 
                </table>
                <br>
                <?php echo CHtml::submitButton(getTranslatedString('Submit'), array('id' => 'updatedamagesubmit', 'name' => 'submit', 'class' => "button")); ?>
            </td>
        </tr>
    </table> 
    <?php echo CHtml::endForm(); ?>
    <?php $this->endWidget(); ?>
</div>

<script type="text/javascript">
    
    function openwindow(path){
        var myWindow = window.open(path, "_blank", "toolbar=no, location=yes, directories=no, status=no, menubar=no, scrollbars=yes, resizable=no, copyhistory=yes, width=800, height=500");
        myWindow.focus();
    }

                    function AjaxMarkDamage(id)
                    {
                        $('#markdamagebutton').html('Please wait...');
                        $("#markdamagebutton").addClass("waitprocess2");
                        $.post('index.php?r=troubleticket/markdamagestatus/', {ticketid: id},
                        function(data)
                        {
                            $("#markdamagebutton").removeClass("waitprocess2");
                            $('#markdamagebutton').html(data);
                        });
                    }
                    
                    $('#updatedamagesubmit').click(function(e){
                        e.preventDefault();
                        if($('#Troubleticket_damagestatus').val() != '' || $('#Troubleticket_notes').val() != ''){
                            $('#damage_status').html('Please wait...');
                            $("#damage_status").addClass("waitprocess2");
                            $.post('index.php?r=troubleticket/damagestatusandnotes', {
                                'id': $('#Troubleticket_id').val(),
                                'damagestatus': $('#Troubleticket_damagestatus').val(),
                                'notes': $('#Troubleticket_notes').val(),
                            }, function(data) {
                                $("#damage_status").removeClass("waitprocess2");
                                $('#damage_status').html("Updated.");
                            });
                        } else {
                            alert("Please select a status or input a note.");
                            return false;
                        }
                    });

</script>


