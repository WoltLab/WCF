#!/usr/bin/env php
<?php
/**
 * @author	Tim Düsterhus
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
if (PHP_SAPI !== 'cli') exit;

// define the wcf-root-dir
define('WCF_DIR', dirname(__FILE__).'/');

// TODO: Fix PACKAGE_ID
define('PACKAGE_ID', 1);

// initiate wcf core
require_once(WCF_DIR.'lib/system/WCF.class.php');
require_once(WCF_DIR.'lib/system/CLIWCF.class.php');
new wcf\system\CLIWCF();
