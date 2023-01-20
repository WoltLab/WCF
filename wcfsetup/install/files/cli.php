#!/usr/bin/env php
<?php

/**
 * @author        Tim Duesterhus
 * @copyright        2001-2019 WoltLab GmbH
 * @license        GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

if (\PHP_SAPI !== 'cli') {
    \http_response_code(400);

    exit;
}

if (\function_exists('posix_getuid') && \posix_getuid() === 0) {
    \fwrite(\STDERR, "Refusing to execute as root.\n");

    exit(1);
}

// include config
require_once(__DIR__ . '/app.config.inc.php');

// initiate wcf core
require_once(WCF_DIR . 'lib/system/WCF.class.php');
require_once(WCF_DIR . 'lib/system/CLIWCF.class.php');
new wcf\system\CLIWCF();
