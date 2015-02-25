<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
// set exception handler
set_exception_handler(array('wcf\system\WCF', 'handleException'));

// set php error handler
set_error_handler(array('wcf\system\WCF', 'handleError'), E_ALL);

// set shutdown function
register_shutdown_function(array('wcf\system\WCF', 'destruct'));

// set autoload function
spl_autoload_register(array('wcf\system\WCF', 'autoload'));

// define escape string shortcut
function escapeString($string) {
	return wcf\system\WCF::getDB()->escapeString($string);
}

// define DOCUMENT_ROOT on IIS if not set
if (PHP_EOL == "\r\n") {
	if (!isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['SCRIPT_FILENAME'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
	}
	if (!isset($_SERVER['DOCUMENT_ROOT']) && isset($_SERVER['PATH_TRANSLATED'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
	}
	
	if (!isset($_SERVER['REQUEST_URI'])) {
		$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
		if (isset($_SERVER['QUERY_STRING'])) {
			$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
		}
	}
}
