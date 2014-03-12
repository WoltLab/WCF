<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
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
