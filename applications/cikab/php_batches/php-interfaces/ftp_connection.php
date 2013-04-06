<?php

/**
 * @category   Cronjobs
 * @package    Integration
 * @subpackage FTPConfig
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

/**
 * Call Global Configration file
 */
require_once __DIR__. '/config.inc.php';

/**
 * Ready state of syslog
 */
openlog("FTPConnectionCron", LOG_PID | LOG_PERROR, LOG_LOCAL0);


/**
 * Check FTP Connection
 */

$ftpConnId = ftp_connect($dbconfigFtp['Host'], $dbconfigFtp['port'], 10);

/**
 * If connection fails update syslog with the error message and display
 * an error message.
 */

if (!$ftpConnId) {
    $syslogmessage = "Some problem in FTP Connection.please check Host Name!";
    syslog(
        LOG_WARNING, "" . $syslogmessage . ""
    );
    exit;
}


/**
 *  Check Authentication after connection
 */
$ftpLoginResult = ftp_login(
    $ftpConnId, $dbconfigFtp['User'], $dbconfigFtp['Password']
);
/**
 * If authentication fails update the syslog.
 */
if (!$ftpLoginResult) {
    $syslogmessage = "Some problem in FTP Connection.please " .
        "check username and password!";
    syslog(
        LOG_WARNING, "" . $syslogmessage . ""
    );
    exit;
}

/*
 * Enable passive mode
 */
ftp_pasv($ftpConnId, true);

/**
 * Check FTP Connection and Authenticate the connection again
 */

if ((!$ftpConnId ) || (!$ftpLoginResult )) {
    $syslogmessage = "Some problem in FTP Connection failed!";
    syslog(
        LOG_WARNING, "" . $syslogmessage . ""
    );
    exit;
}
