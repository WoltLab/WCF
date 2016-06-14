<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
// include config
require_once(__DIR__.'/app.config.inc.php');

// initiate wcf core
require_once(WCF_DIR.'lib/system/WCF.class.php');
new wcf\system\WCF();
