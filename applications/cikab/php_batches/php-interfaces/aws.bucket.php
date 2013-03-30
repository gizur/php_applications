<?php

/**
 * @category   Cronjobs
 * @package    Integration
 * @subpackage SQSConfig
 * @author     Prabhat Khera <prabhat.khera@essindia.co.in>
 * @version    SVN: $Id$
 * @link       href="http://gizur.com"
 * @license    Commercial license
 * @copyright  Copyright (c) 2012, Gizur AB, 
 * <a href="http://gizur.com">Gizur Consulting</a>, All rights reserved.
 *
 * purpose : Connect to Amazon SQS through aws-php-sdk
 * Coding standards:
 * http://pear.php.net/manual/en/standards.php
 *
 * PHP version 5.3
 *
 */
/*
 * Load the confid and Amazon SDK
 */
require_once __DIR__ . '/config.inc.php';
require_once __DIR__ . '/../../../../lib/aws-php-sdk/sdk.class.php';

/**
  Instansiate AmazonSQS
 */
try {
    $sThree = new AmazonS3();
    $filename = "SET.GZ.FTP.IN.BST.201303290436.283124";
    $fileContent = "HEADERGIZUR           2013032904369188M256      RUTIN   .130KF27777100   mottagn
ing initierad                                                                   
      0012831241+03751+038774554+226130331+039130329+040774554+189202035002+C   
      RUTIN   .130KF51125185   Mottagning avslutad    BYTES/BLOCKS/RETRIES=1084 
/5    /0";
    $responseSThree = $sThree->create_object(
        $amazonSThree['bucket'], $filename, array(
        'body' => $fileContent,
        'contentType' => 'plain/text',
        'headers' => array(
            'Cache-Control' => 'max-age',
            'Content-Language' => 'en-US',
            'Expires' =>
            'Thu, 01 Dec 1994 16:00:00 GMT',
        )
        )
    );

    if ($responseSThree->isOK()) {
        echo "Second upload successful!";
    } else {
        print_r($responseSThree);
    }
} catch (SQS_Exception $e) {
    syslog(LOG_WARNING, "Unable to connect to Amazon SQS.");
    die("Unable to connect to Amazon SQS.");
}
