<?php

namespace wcf\command\file;

use wcf\data\file\File;

/**
 * Deletes a file permanently.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteFile
{
    public function __construct(
        private readonly File $file,
    ) {}

    public function __invoke(): void
    {
        new DeleteFiles([$this->file])();
    }
}
