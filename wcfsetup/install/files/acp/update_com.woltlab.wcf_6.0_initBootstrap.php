<?php

/**
 * Creates an empty bootstrap file if it does not exist. This prevents a misleading
 * error message added to the log when it is initialized for the first time.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

namespace wcf\acp;

use wcf\system\io\AtomicWriter;

// Variable taken from 'WCF::BOOTSTRAP_LOADER' which is not defined yet.
$BOOTSTRAP_LOADER = \WCF_DIR . '/lib/bootstrap.php';

if (\file_exists($BOOTSTRAP_LOADER)) {
    return;
}

$writer = new AtomicWriter($BOOTSTRAP_LOADER);
$writer->write(
    <<<'EOT'
        <?php
        return [];
        EOT
);
$writer->flush();
