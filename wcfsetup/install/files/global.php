<?php

/**
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

require_once(__DIR__ . '/app.config.inc.php');

// Deny access to the frontend until the WCFSetup has completed.
if (defined('PACKAGE_ID') && PACKAGE_ID === 0) {
    \http_response_code(500);

    exit;
}

require_once(WCF_DIR . 'lib/system/WCF.class.php');
new wcf\system\WCF();
