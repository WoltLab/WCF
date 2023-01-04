<?php

/**
 * Recreates all favicon thumbnails.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\data\style\StyleEditor;
use wcf\data\style\StyleList;
use wcf\system\image\ImageHandler;

$styleList = new StyleList();
$styleList->readObjects();

$images = [
    'favicon-32x32.png' => 32,
    'android-chrome-192x192.png' => 192,
    'android-chrome-256x256.png' => 256,
    'apple-touch-icon.png' => 180,
    'mstile-150x150.png' => 150,
];

foreach ($styleList as $style) {
    $style->loadVariables();
    $variables = $style->getVariables();
    $styleEditor = new StyleEditor($style);
    
    foreach (['png', 'jpg', 'gif'] as $extension) {
        $templatePath = $style->getAssetPath() . "favicon-template." . $extension;
        if (\file_exists($templatePath)) {
            $adapter = ImageHandler::getInstance()->getAdapter();
            $adapter->loadFile($templatePath);

            foreach ($images as $filename => $length) {
                $thumbnail = $adapter->createThumbnail($length, $length);
                $adapter->writeImage($thumbnail, $style->getAssetPath() . $filename);
                // Clear thumbnail as soon as possible to free up the memory.
                $thumbnail = null;
            }

            break;
        }
    }
}
