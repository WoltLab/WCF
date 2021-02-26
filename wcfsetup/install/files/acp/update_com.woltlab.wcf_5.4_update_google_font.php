<?php

/**
 * Re-downloads all Google Fonts.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\background\BackgroundQueueHandler;
use wcf\system\background\job\DownloadGoogleFontBackgroundJob;

$families = \glob(WCF_DIR . 'font/families/*/font.css');

foreach ($families as $css) {
    $family = \basename(\dirname($css));

    BackgroundQueueHandler::getInstance()->enqueueIn(
        new DownloadGoogleFontBackgroundJob($family),
        10 * 60
    );
}
