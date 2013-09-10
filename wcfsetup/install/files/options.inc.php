<?php
/**
 * default options.inc.php for package installation of package com.woltlab.wcf.
 *
 * @author	Marcel Werk 
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define('PACKAGE_ID', 0);

define('COOKIE_PREFIX', 'wcf_');
define('COOKIE_PATH', '');
define('COOKIE_DOMAIN', '');

define('HTTP_ENABLE_NO_CACHE_HEADERS', 0);
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
define('ENABLE_BENCHMARK', 0);
