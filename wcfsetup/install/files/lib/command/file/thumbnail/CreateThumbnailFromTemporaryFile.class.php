<?php

namespace wcf\command\file\thumbnail;

use wcf\data\file\File;
use wcf\data\file\thumbnail\FileThumbnail;
use wcf\data\file\thumbnail\FileThumbnailBuilder;
use wcf\system\file\processor\ThumbnailFormat;

/**
 * Promotes a freshly generated thumbnail into a persisted thumbnail of a file.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateThumbnailFromTemporaryFile
{
    public function __construct(
        private readonly File $file,
        private readonly ThumbnailFormat $format,
        private readonly string $pathname,
    ) {}

    public function __invoke(): FileThumbnail
    {
        [$width, $height] = \getimagesize($this->pathname);

        $fileThumbnail = FileThumbnailBuilder::forCreate()
            ->setFile($this->file)
            ->setFormat($this->format)
            ->setFileHash(\hash_file('sha256', $this->pathname))
            ->setFileExtension('webp')
            ->setDimensions($width, $height)
            ->create();

        $filePath = $fileThumbnail->getPath();
        if (!\is_dir($filePath)) {
            \mkdir($filePath, recursive: true);
        }

        \rename(
            $this->pathname,
            $filePath . $fileThumbnail->getSourceFilename()
        );

        return $fileThumbnail;
    }
}
