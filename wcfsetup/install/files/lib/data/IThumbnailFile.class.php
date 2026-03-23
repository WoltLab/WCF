<?php

namespace wcf\data;

/**
 * Every database object representing a file supporting thumbnails should implement
 * this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
interface IThumbnailFile extends IFile
{
    /**
     * Returns the link to the thumbnail file with the given size.
     *
     * @return string
     */
    public function getThumbnailLink(string $size);

    /**
     * Returns the physical location of the thumbnail file with the given size.
     *
     * @return string
     */
    public function getThumbnailLocation(string $size);

    /**
     * Returns the available thumbnail sizes.
     *
     * @return array<string, array{
     *  height: int,
     *  retainDimensions: bool|int,
     *  width: int,
     * }>
     */
    public static function getThumbnailSizes();
}
