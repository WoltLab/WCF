<?php

namespace wcf\system\file\command;

use wcf\data\file\File;
use wcf\system\WCF;

/**
 * Replaces the physical file of an entry with a new file. If there are any
 * existing thumbnails, those will be regenerated immediately.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\file\ReplaceFileSource` instead.
 */
final class ReplaceFileSource
{
    public function __construct(
        private readonly File $file,
        private readonly string $pathname,
        private readonly ?string $filename = null,
        private readonly ?bool $regenerateThumbnails = true,
    ) {}

    public function __invoke(): File
    {
        return (new \wcf\command\file\ReplaceFileSource(
            $this->file,
            $this->pathname,
            $this->filename,
            $this->regenerateThumbnails
        ))();
    }
}
