<?php

namespace wcf\event\file;

use wcf\data\file\File;
use wcf\event\IPsr14Event;

/**
 * Fired when a file was uploaded before it is returned to the callee.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class UploadCompleted implements IPsr14Event
{
    public function __construct(
        private File $file,
    ) {}

    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * Reloads the file to fetch updated values. This must be called whenever
     * the uploaded files has been modified by a listener.
     */
    public function reload(): void
    {
        $this->file = new File($this->file->fileID);
    }
}
