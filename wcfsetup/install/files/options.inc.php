<?php
/**
 * default options.inc.php for package installation of package com.woltlab.wcf.
 *
 * @author	Marcel Werk 
 * @copyright	2001-2007 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define('COOKIE_PREFIX', 'wcf_');
define('COOKIE_PATH', '');
define('COOKIE_DOMAIN', '');

define('HTTP_ENABLE_NO_CACHE_HEADERS', 0);
define('HTTP_ENABLE_GZIP', 0);
define('HTTP_GZIP_LEVEL', 1);

define('BLACKLIST_IP_ADDRESSES', '');
define('BLACKLIST_USER_AGENTS', '');
define('BLACKLIST_HOSTNAMES', '');

define('SESSION_TIMEOUT', 3600);

define('CACHE_SOURCE_TYPE', 'disk');
define('ENABLE_SESSION_DATA_CACHE', 0);
define('MODULE_MASTER_PASSWORD', 1);
define('TIMEZONE', 'Europe/Berlin');
