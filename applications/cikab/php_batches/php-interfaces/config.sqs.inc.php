<?php

/**
 * @category   Cronjobs
 * @package    Integration
 * @subpackage DatabaseConfig
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
?>
<?php

require_once __DIR__ . '/config.inc.php';
require_once __DIR__ . '/../../../../lib/aws-php-sdk/sdk.class.php';

/**
  Instansiate AmazonSQS
 */
try {
    $sqs = new AmazonSQS();
} catch (SQS_Exception $e) {
    syslog(LOG_WARNING, "Unable to connect to Amazon SQS.");
    die("Unable to connect to Amazon SQS.");
}
