#!/usr/bin/env php
<?php
// @codingStandardsIgnoreFile
/**
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
if (PHP_SAPI !== 'cli') exit;

// include config
require_once(__DIR__.'/app.config.inc.php');

// initiate wcf core
require_once(WCF_DIR.'lib/system/WCF.class.php');
require_once(WCF_DIR.'lib/system/CLIWCF.class.php');
new wcf\system\CLIWCF();
