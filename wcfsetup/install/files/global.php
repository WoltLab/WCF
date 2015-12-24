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
	define('PACKAGE_ID', 1);
	/* TODO:
	@header("HTTP/1.0 404 Not Found");
	exit;
	*/
}

// define the wcf-root-dir
define('WCF_DIR', dirname(__FILE__).'/');

// initiate wcf core
require_once(WCF_DIR.'lib/system/WCF.class.php');
new wcf\system\WCF();
