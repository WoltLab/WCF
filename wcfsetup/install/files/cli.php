#!/usr/bin/env php
<?php
// @codingStandardsIgnoreFile
/**
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
if (PHP_SAPI !== 'cli') exit;
// define the wcf-root-dir
define('WCF_DIR', dirname(__FILE__).'/');

// initiate wcf core
require_once(WCF_DIR.'lib/system/WCF.class.php');
require_once(WCF_DIR.'lib/system/CLIWCF.class.php');
new wcf\system\CLIWCF();
