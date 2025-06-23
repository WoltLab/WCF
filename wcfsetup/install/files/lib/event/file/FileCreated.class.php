<?php

namespace wcf\event\file;

use wcf\data\file\File;
use wcf\event\IPsr14Event;

/**
 * Fired when a file was created before it is returned to the callee.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class FileCreated implements IPsr14Event
{
    public function __construct(
        private File $file,
    ) {}

    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * Reloads the file to fetch updated values.
     */
    public function reload(): void
    {
        $this->file = new File($this->file->fileID);
    }
}
