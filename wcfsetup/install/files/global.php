<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
// ignore direct access
if (!defined('PACKAGE_ID')) {
	@header("HTTP/1.0 404 Not Found");
	exit;
}

// define the wcf-root-dir
define('WCF_DIR', dirname(__FILE__).'/');

// APC below 3.1.4 breaks PHP's late static binding
if (extension_loaded('apc') && strnatcmp(phpversion('apc'), '3.1.4') < 0) {
	apc_clear_cache('opcode');
}

// initiate wcf core
require_once(WCF_DIR.'lib/system/WCF.class.php');
new wcf\system\WCF();
