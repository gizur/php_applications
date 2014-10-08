<?php
$baseUrl = Yii::app()->baseUrl;
$cs = Yii::app()->getClientScript();
$cs->registerCoreScript('jquery');
$cs->registerScriptFile($baseUrl . '/js/jquery.dataTables.min.js');
$cs->registerScriptFile($baseUrl . '/js/jquery.validate.js');
$cs->registerCssFile($baseUrl . '/css/jquery.dataTables.css');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="language" content="en" />

        <!-- blueprint CSS framework -->
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
        <!--[if lt IE 8]>
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
        <![endif]-->

        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />

        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    </head>
    <style>
       .msgerror {
            color:red;
              }
       .msgsuccess {
            color:green;
                }
    </style>

    <?php
    if (Yii::app()->session['Lang'] == "") {
        $lang = 'en';
    } else {
        $lang = Yii::app()->session['Lang'];
    }
    include_once 'protected/extensions/language/' . $lang . '.php';
    ?>
    <body>
        <div class="container" id="page">

            <div id="header">

                <div id="logo"><span style="float:left; padding:10px 20px;"><?php echo CHtml::encode(Yii::app()->name); ?></span> <span style="float:right;padding:0px 20px;"><img src="<?php echo Yii::app()->request->baseUrl; ?>/images/final-logo-gizur-60.png" width="60px"></span></div>
                <!-- header -->

                <div id="mainmenu">
                    <?php
                    $user = Yii::app()->session['username'];
                    $querystring = $_SERVER['QUERY_STRING'];
                    $strTemp1 = trim($user);

                    /*
                      if($user==null);
                      {
                      $returnUrl=Yii::app()->homeUrl;
                      //$this->redirect($returnUrl);
                      //exit;
                      }
                     */
                    if (!empty($user)) {
                        $loginstatus = 0;
                    } else {
                        $loginstatus = 1;
                    }
                    ?>
                    <?php
                    $items = array();   
                    
                    array_push($items, array('label' => getTranslatedString('Survey'),'linkOptions'=>array('id'=>'trouble'), 'url' => array('/troubleticket/surveylist')));
                    if(Yii::app()->params['showContactTab']) {
                        array_push($items, array('label' => getTranslatedString('Contacts'),'linkOptions'=>array('id'=>'contacts'), 'url' => array('/contacts/list')));
                    }
                    if(Yii::app()->params['showAssetTab']) {
                        array_push($items, array('label' => getTranslatedString('Assets'),'linkOptions'=>array('id'=>'assets'), 'url' => array('/assets/list')));
                    }
                    array_push($items, array('label' => getTranslatedString('Change Password'),'linkOptions'=>array('id'=>'password'), 'url' => array('/site/changepassword')));
                    array_push($items, array('label' => getTranslatedString('Login'), 'url' => array('/site/login'), 'visible' => $loginstatus));
                    array_push($items, array('label' => getTranslatedString('Logout'), 'url' => array('/site/logout'), 'visible' => !$loginstatus));
                    
                    if ($user != 'Guest' && !empty($user)) {
                        $this->widget('zii.widgets.CMenu', array(
                            'items' => $items,
                        ));
                    }
                    ?>
                    <?php
                    if (!empty($user)) {
                        ?>
                        <div style="float:right;padding-right:20px;"> <?php echo getTranslatedString('Welcome'); ?> &nbsp;
                            <?php echo CHtml::encode($user); ?></div>
                    <?php } ?>
                </div>
            </div><!-- mainmenu -->
            <?php if (isset($this->breadcrumbs)): ?>
                <?php $this->widget('zii.widgets.CBreadcrumbs', array('links' => $this->breadcrumbs,)); ?><!-- breadcrumbs -->
            <?php endif ?>
                <?php
                foreach (Yii::app()->user->getFlashes() as $index => $value) {
                    ?>
                    <div class="msg<?php echo $index; ?>" style="padding-left:30px; font-size: 14px;"><?php echo $value; ?></div>
                    <?php }
                ?>


            <?php echo $content; ?>

            <div class="clear"></div>
            <!-- footer -->
        </div><!-- page -->

    </body>
</html>
