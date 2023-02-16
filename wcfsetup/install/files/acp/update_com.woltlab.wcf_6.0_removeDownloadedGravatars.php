<?php

/**
 * Removes the downloaded gravatars.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\package\SplitNodeException;

$deleted = 0;

foreach (new \DirectoryIterator(WCF_DIR . 'images/avatars/gravatars/') as $fileInfo) {
    if (!$fileInfo->isFile()) {
        continue;
    }

    if (!\preg_match('/^[0-9a-f]{32}-[0-9]+\.(png|gif|jpe?g)$/', $fileInfo->getBasename())) {
        continue;
    }

    \unlink($fileInfo->getPathname());
    $deleted++;

    if ($deleted > 500) {
        throw new SplitNodeException();
    }
}
