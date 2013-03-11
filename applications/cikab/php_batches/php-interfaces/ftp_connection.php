<?php

/**
 * created date : 02/06/2012
 * created by : anil singh
 * @author anil singh <anil-singh@essindia.co.in>
 * flow : Connect to your FTP Server 
 * 		  
 * modify date : 02/06/2012
 */
/**
 * Call Global Configration file
 */
require_once 'config.inc.php';

/**
 * ready state of syslog
 */
openlog("FTPConnectionCron", LOG_PID | LOG_PERROR, LOG_LOCAL0);


/**
 * Check FTP Connection
 */
$ftpConnId = ftp_connect($dbconfigFtp['Host'], $dbconfigFtp['port']);
/**
 * Check FTP Connection if any issue in ftp then manage error in syslog
 */
if (!$ftpConnId) {
    $syslogmessage = "Some problem in FTP Connection.please check Host Name!";
    syslog(
        LOG_WARNING, "" . $syslogmessage . ""
    );
    exit;
}


/**
 *  After Connect to the FTP then Check Auth..
 */
$ftpLoginResult = ftp_login(
    $ftpConnId, $dbconfigFtp['User'], $dbconfigFtp['Password']
);
if (!$ftpLoginResult) {
    $syslogmessage = "Some problem in FTP Connection.please " .
        "check username and password!";
    syslog(
        LOG_WARNING, "" . $syslogmessage . ""
    );
    exit;
}

ftp_pasv($ftpConnId, true);

/* * *
 * Check FTP Connection and Auth will be success or not
 */

if ((!$ftpConnId ) || (!$ftpLoginResult )) {
    $syslogmessage = "Some problem in FTP Connection failed!";
    syslog(
        LOG_WARNING, "" . $syslogmessage . ""
    );
    exit;
}
