<?php

/**
 * vTiger wrapper. Environemnt dependent configurations
 *
 * @package    vtiger wrapper
 * @subpackage Config
 * @author     Jonas Colmsjö <jonas.colmsjo@gizur.com>
 * @version    SVN: $Id$
 *
 * @license    Commercial license
 * @copyright  Copyright (c) 2012, Gizur AB, <a href="http://gizur.com">Gizur Consulting</a>, All rights reserved.
 *
 * Coding standards:
 * http://pear.php.net/manual/en/standards.php
 *
 * PHP version 5
 */
 
 
/** 
 * The wrapper needs redirects to be configured. This is either performed in the Apache configuration file,
 * i.e. httpd.conf etc. or in the .htaccess file. 
 *
 * Example of configuration snippet to put in httpd.conf. Make sure that the vtiger path is changed according
 * to the actual path used in your installation. 
 *
 * <Directory /var/www/developers/jonas/vtwrapper/test1>
 *
 *        RewriteEngine on
 *
 *		  # Make sure that index.php is used for empty path
 *        RewriteRule   ^$  index\.php [L]
 *
 *		  # Make sure that index.php is not redirected
 *        RewriteRule   ^index\.php$  index\.php [L]
 *
 *        # Make sure nothing is done once vtiger dir has been added
 *        RewriteRule   ^/var/www/lib/vtiger-5.4.0/(.*)$  /var/www/lib/vtiger-5.4.0/$1 [L]
 *
 *        # Add vtiger path to all URI:s
 *        RewriteRule   ^(.*)$  /var/www/lib/vtiger-5.4.0/$1 [L]
 *
 * </Directory>
 *
 */

/**
 * The domain where the wrapper has been installed (needed for JavaScript CORS calls)
 */
define("VTWRAPPER_DOMAIN", 'developer1.gizurcloud.com');


/**
 * Path to vtiger installation
 */
define("VTWRAPPER_VTIGER_PATH", '/var/www/html/lib/vtiger-5.4.0');


/**
 * Path to wrapper
 */
define("VTWRAPPER_PATH", '/var/www/html/lib');


/**
 * Set debugging flag that controls if PhpConsole should be started
 * or not.
 *
 */
define("VTWRAPPER_DEBUG", False);


/**
 * Start PhpConsole if debugging is enabled
 *
 */

if(VTWRAPPER_DEBUG) {	

	require_once('/var/www/lib/PhpConsole/PhpConsole.php');
	PhpConsole::start(true, true, dirname(__FILE__));
	debug('Starting phpConsole...');

}


/**
 * Log to PhpConsole if debugging is enabled
 *
 */

function vtwrapper_debug($str) {
	
	// debug only if debugging is enabled
	if(VTWRAPPER_DEBUG) {	
		debug($str);
	}	
}


?>
