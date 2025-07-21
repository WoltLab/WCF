<?php

namespace wcf\system\file\command;

use wcf\data\file\File;

/**
 * Converts an image to its WebP variant by replacing it with it.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ReplaceWithWebpVariant
{
    public function __construct(
        private readonly File $file,
    ) {}

    public function __invoke(): File
    {
        $pathnameWebp = $this->file->getPathnameWebp();
        if ($pathnameWebp === null) {
            return $this->file;
        }

        // We cannot reliably detect if a GIF is animated without ImageMagick
        // and the generated WebP variant will always be static.
        if ($this->file->mimeType === 'image/gif') {
            return $this->file;
        }

        $command = new ReplaceFileSource(
            $this->file,
            $pathnameWebp,
            $this->getNewFilename(),
        );
        $command();

        return new File($this->file->fileID);
    }

    private function getNewFilename(): string
    {
        $filename = \preg_replace(
            '~\.(?:jpe?g|png)$~i',
            '',
            $this->file->filename,
        );

        return \sprintf(
            "%s.webp",
            $filename,
        );
    }
}
