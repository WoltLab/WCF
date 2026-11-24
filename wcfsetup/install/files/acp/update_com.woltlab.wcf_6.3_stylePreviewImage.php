<?php

/**
 * Deletes the preview images of the styles, the columns are dropped afterwards.
 *
 * @author    Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\system\WCF;

$sql = "SELECT  image, image2x
        FROM    wcf1_style";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
while ($row = $statement->fetchArray()) {
    foreach ([$row['image'], $row['image2x']] as $image) {
        if ($image === '') {
            continue;
        }

        $filename = \WCF_DIR . 'images/' . $image;
        if (\is_file($filename)) {
            @\unlink($filename);
        }
    }
}
