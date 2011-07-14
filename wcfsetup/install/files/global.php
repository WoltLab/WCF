<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category 	Community Framework
 */
// define the wcf-root-dir
define('WCF_DIR', dirname(__FILE__).'/');

// initiate wcf core
require_once(WCF_DIR.'lib/system/WCF.class.php');
new wcf\system\WCF();
