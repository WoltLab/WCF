<?php

/**
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

// phpcs:disable PSR1.Files.SideEffects

// Constant to get relative path to the wcf-root-dir.
// This constant is already set in each package which got an own config.inc.php
if (!\defined('RELATIVE_WCF_DIR')) {
    \define('RELATIVE_WCF_DIR', '../');
}

// include config
require_once(__DIR__ . '/../app.config.inc.php');

// starting wcf acp
require_once(WCF_DIR . 'lib/system/WCF.class.php');
require_once(WCF_DIR . 'lib/system/WCFACP.class.php');
new wcf\system\WCFACP();
