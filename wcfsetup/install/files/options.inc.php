<?php
/**
 * Default options.inc.php for package installation of package com.woltlab.wcf.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define('LAST_UPDATE_TIME', TIME_NOW);

$prefix = 'wsc_';
if (file_exists(WCF_DIR . 'cookiePrefix.txt')) {
	// randomized cookie prefix during setup
	$prefix = file_get_contents(WCF_DIR . 'cookiePrefix.txt');
}
define('COOKIE_PREFIX', $prefix);

define('COOKIE_PATH', '');
define('COOKIE_DOMAIN', '');

define('HTTP_ENABLE_GZIP', 0);
define('HTTP_GZIP_LEVEL', 1);
define('HTTP_SEND_X_FRAME_OPTIONS', 0);

define('BLACKLIST_IP_ADDRESSES', '');
define('BLACKLIST_USER_AGENTS', '');
define('BLACKLIST_HOSTNAMES', '');

define('SESSION_TIMEOUT', 3600);
define('SESSION_VALIDATE_IP_ADDRESS', 0);
define('SESSION_VALIDATE_USER_AGENT', 0);

define('CACHE_SOURCE_TYPE', 'disk');
define('IMAGE_ADAPTER_TYPE', 'gd');
define('MODULE_MASTER_PASSWORD', 0);
define('TIMEZONE', 'Europe/Berlin');

define('ENABLE_DEBUG_MODE', 1);
define('ENABLE_PRODUCTION_DEBUG_MODE', 1);
define('ENABLE_BENCHMARK', 0);
if (!defined('ENABLE_ENTERPRISE_MODE')) define('ENABLE_ENTERPRISE_MODE', 0);
define('EXTERNAL_LINK_TARGET_BLANK', 0);
define('URL_LEGACY_MODE', 0);
define('URL_TO_LOWERCASE', 1);
define('SEARCH_ENGINE', 'mysql');
define('SHOW_VERSION_NUMBER', 1);
define('LANGUAGE_USE_INFORMAL_VARIANT', 0);
define('URL_OMIT_INDEX_PHP', 0);
define('VISITOR_USE_TINY_BUILD', 0);
define('ENABLE_DEVELOPER_TOOLS', 0);
define('LOG_MISSING_LANGUAGE_ITEMS', 0);
define('FORCE_LOGIN', 0);

define('WCF_OPTION_INC_PHP_SUCCESS', true);
