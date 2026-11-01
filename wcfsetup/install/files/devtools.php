#!/usr/bin/env php
<?php

/**
 * Non-interactive command line interface of the developer tools.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */

if (\PHP_SAPI !== 'cli') {
    \http_response_code(400);

    exit;
}

if (\function_exists('posix_getuid') && \posix_getuid() === 0) {
    \fwrite(\STDERR, "Refusing to execute as root.\n");

    exit(1);
}

\define('PACKAGE_ID', 1);

// include config
require_once(__DIR__ . '/app.config.inc.php');

// initiate wcf core
require_once(WCF_DIR . 'lib/system/WCF.class.php');

exit(new \wcf\system\devtools\DevtoolsCLIWCF()->run(\array_slice($_SERVER['argv'], 1)));
