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
$conn_id = ftp_connect($dbconfig_ftphost['Host']);
/**
 * Check FTP Connection if any issue in ftp then manage error in syslog
 */
if (!$conn_id) {
    $syslogmessage = "Some problem in FTP Connection.please check Host Name!";
    syslog(LOG_WARNING, "" . $syslogmessage . "");
    exit;
}


/**
 *  After Connect to the FTP then Check Auth..
 */
$login_result = ftp_login($conn_id, $dbconfig_ftpuser['User'], $dbconfig_ftppassword['Password']);
if (!$login_result) {
    $syslogmessage = "Some problem in FTP Connection.please check username and password!";
    syslog(LOG_WARNING, "" . $syslogmessage . "");
    exit;
}

ftp_pasv($conn_id, true);

/* * *
 * Check FTP Connection and Auth will be success or not
 */

if ((!$conn_id ) || (!$login_result )) {
    $syslogmessage = "Some problem in FTP Connection failed!";
    syslog(LOG_WARNING, "" . $syslogmessage . "");
    exit;
}
?>
