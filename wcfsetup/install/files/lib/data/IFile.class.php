<?php

namespace wcf\data;

/**
 * Every database object representing a file should implement this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @property-read   string $fileType   type of the physical attachment file
 * @property-read   int $isImage    is `1` if the file is an image, otherwise `0`
 * @property-read   int $width      width of the file if `$isImage` is `1`, otherwise `0`
 * @property-read   int $height     height of the file if `$isImage` is `1`, otherwise `0`
 */
interface IFile extends IStorableObject
{
    /**
     * Returns the physical location of the file.
     *
     * @return  string
     */
    public function getLocation();
}
