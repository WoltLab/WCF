<?php

namespace wcf\data\media;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit media files.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method static Media   create(array $parameters = [])
 * @method      Media   getDecoratedObject()
 * @mixin       Media
 */
class MediaEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Media::class;

    /**
     * Deletes the physical files of the media file.
     */
    public function deleteFiles()
    {
        @\unlink($this->getLocation());

        // delete thumbnails
        if ($this->isImage) {
            foreach (Media::getThumbnailSizes() as $size => $data) {
                @\unlink($this->getThumbnailLocation($size));
            }
        }
    }
}
