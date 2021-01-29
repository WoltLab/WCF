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
use wcf\system\package\SplitNodeException;
use wcf\system\style\StyleHandler;

foreach (StyleHandler::getInstance()->getStyles() as $style) {
    if (!$style->coverPhotoExtension) {
        continue;
    }

    if (\file_exists($style->getCoverPhotoLocation(true))) {
        continue;
    }

    $styleEditor = new StyleEditor($style);
    $result = $styleEditor->createCoverPhotoVariant();
    if ($result === null) {
        continue;
    }

    // Queue the script again to prevent running into a timeout.
    throw new SplitNodeException();
}
