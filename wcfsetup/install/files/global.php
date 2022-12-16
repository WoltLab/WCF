<?php

/**
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

// include config
require_once(__DIR__ . '/app.config.inc.php');

// Make the frontend inaccessible until WCFSetup completes.
if (!PACKAGE_ID) {
	\http_response_code(500);
	exit;
}

// initiate wcf core
require_once(WCF_DIR . 'lib/system/WCF.class.php');
new wcf\system\WCF();
