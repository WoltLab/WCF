<?php

namespace wcf\data\file;

use wcf\action\FileDownloadAction;
use wcf\data\DatabaseObject;
use wcf\data\file\thumbnail\FileThumbnail;
use wcf\system\file\processor\FileProcessor;
use wcf\system\file\processor\IFileProcessor;
use wcf\system\request\LinkHandler;
use wcf\util\StringUtil;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @property-read int $fileID
 * @property-read string $filename
 * @property-read int $fileSize
 * @property-read string $fileHash
 * @property-read string $typeName
 * @property-read string $mimeType
 * @property-read int|null $width
 * @property-read int|null $height
 */
class File extends DatabaseObject
{
    /** @var array<string, FileThumbnail> */
    private array $thumbnails = [];

    public function getFilename(): string
    {
        return \sprintf(
            '%d-%s.bin',
            $this->fileID,
            $this->fileHash,
        );
    }

    public function getPath(): string
    {
        $folderA = \substr($this->fileHash, 0, 2);
        $folderB = \substr($this->fileHash, 2, 2);

        return \sprintf(
            \WCF_DIR . '_data/private/fileUpload/%s/%s/',
            $folderA,
            $folderB,
        );
    }

    public function getPathname(): string
    {
        return $this->getPath() . $this->getFilename();
    }

    public function getLink(): string
    {
        return LinkHandler::getInstance()->getControllerLink(
            FileDownloadAction::class,
            ['id' => $this->fileID]
        );
    }

    public function getProcessor(): ?IFileProcessor
    {
        return FileProcessor::getInstance()->forTypeName($this->typeName);
    }

    public function isImage(): bool
    {
        return match ($this->mimeType) {
            'image/gif' => true,
            'image/jpeg' => true,
            'image/png' => true,
            'image/webp' => true,
            default => false,
        };
    }

    public function canDelete(): bool
    {
        $processor = $this->getProcessor();
        if ($processor === null) {
            return true;
        }

        return $processor->canDelete($this);
    }

    public function addThumbnail(FileThumbnail $thumbnail): void
    {
        $this->thumbnails[$thumbnail->identifier] = $thumbnail;
    }

    public function getThumbnail(string $identifier): ?FileThumbnail
    {
        return $this->thumbnails[$identifier] ?? null;
    }

    public function toHtmlElement(): string
    {
        $thumbnails = [];
        foreach ($this->thumbnails as $thumbnail) {
            $thumbnails[] = [
                'identifier' => $thumbnail->identifier,
                'link' => $thumbnail->getLink(),
            ];
        }

        // TODO: Icon and preview url is missing.
        return \sprintf(
            <<<'EOT'
                <woltlab-core-file
                    file-id="%d"
                    data-filename="%s"
                    data-mime-type="%s"
                    data-thumbnails="%s"
                ></woltlab-core-file>
                EOT,
            $this->fileID,
            StringUtil::encodeHTML($this->filename),
            StringUtil::encodeHTML($this->mimeType),
            StringUtil::encodeHTML(\json_encode($thumbnails)),
        );
    }
}
