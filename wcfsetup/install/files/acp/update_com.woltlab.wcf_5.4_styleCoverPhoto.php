<?php

/**
 * Generates the WebP variant for cover photos of styles.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\data\style\StyleEditor;
use wcf\data\style\StyleList;
use wcf\system\package\SplitNodeException;

$styleList = new StyleList();
$styleList->readObjects();

foreach ($styleList as $style) {
    if (!$style->coverPhotoExtension) {
        continue;
    }

    if (\file_exists($style->getCoverPhotoLocation(true))) {
        continue;
    }

    $styleEditor = new StyleEditor($style);

    // If the cover photo does not exist ...
    if (!\file_exists($style->getCoverPhotoLocation(false))) {
        // ... then the database information is wrong and we clear the cover photo.
        $styleEditor->update([
            'coverPhotoExtension' => '',
        ]);

        continue;
    }

    $result = $styleEditor->createCoverPhotoVariant();
    if ($result === null) {
        continue;
    }

    // Queue the script again to prevent running into a timeout.
    throw new SplitNodeException();
}
