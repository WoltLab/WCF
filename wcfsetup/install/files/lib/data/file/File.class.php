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
 * @property-read string $fileExtension
 * @property-read string $secret
 * @property-read int|null $objectTypeID
 * @property-read string $mimeType
 * @property-read int|null $width
 * @property-read int|null $height
 */
class File extends DatabaseObject
{
    /**
     * List of common file extensions that are always safe to be served directly
     * by the webserver.
     *
     * @var array<string, string>
     */
    public const SAFE_FILE_EXTENSIONS = [
        'avif' => 'image/avif',
        'bz2' => 'application/x-bzip2',
        'gif' => 'image/gif',
        'gz' => 'application/gzip',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'rar' => 'application/vnd.rar',
        'svg' => 'image/svg+xml',
        'tar' => 'application/x-tar',
        'tiff' => 'image/tiff',
        'txt' => 'text/plain',
        'webm' => 'video/webm',
        'webp' => 'image/webp',
        'zip' => 'application/zip',
    ];

    /** @var array<string, FileThumbnail> */
    private array $thumbnails = [];

    public function getSourceFilename(): string
    {
        return \sprintf(
            '%d-%s-%s.%s',
            $this->fileID,
            $this->secret,
            $this->fileHash,
            $this->fileExtension,
        );
    }

    public function getPath(): string
    {
        $folderA = \substr($this->fileHash, 0, 2);
        $folderB = \substr($this->fileHash, 2, 2);

        return \sprintf(
            \WCF_DIR . '_data/%s/files/%s/%s/',
            $this->fileExtension === 'bin' ? 'private' : 'public',
            $folderA,
            $folderB,
        );
    }

    public function getPathname(): string
    {
        return $this->getPath() . $this->getSourceFilename();
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
        return FileProcessor::getInstance()->getProcessorById($this->objectTypeID);
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

    public function toHtmlElement(array $metaData): string
    {
        $thumbnails = [];
        foreach ($this->thumbnails as $thumbnail) {
            $thumbnails[] = [
                'identifier' => $thumbnail->identifier,
                'link' => $thumbnail->getLink(),
            ];
        }

        return \sprintf(
            <<<'EOT'
                <woltlab-core-file
                    file-id="%d"
                    data-filename="%s"
                    data-file-size="%s"
                    data-mime-type="%s"
                    data-thumbnails="%s"
                    data-meta-data="%s"
                    data-link="%s"
                ></woltlab-core-file>
                EOT,
            $this->fileID,
            StringUtil::encodeHTML($this->filename),
            $this->fileSize,
            StringUtil::encodeHTML($this->mimeType),
            StringUtil::encodeHTML(\json_encode($thumbnails)),
            StringUtil::encodeHTML(\json_encode($metaData)),
            StringUtil::encodeHTML($this->getLink()),
        );
    }

    /**
     * Returns the file extension that is always safe for the delivery by the
     * webserver. If the file extension cannot be detected or is not among the
     * list of allowed file extension then 'bin' is returned.
     */
    public static function getSafeFileExtension(string $mimeType, string $filename): string
    {
        $fileExtension = \array_search($mimeType, self::SAFE_FILE_EXTENSIONS, true);
        if (\is_string($fileExtension)) {
            return $fileExtension;
        }

        if (\str_contains($filename, '.')) {
            $fileExtension = \mb_substr(
                $filename,
                \mb_strrpos($filename, '.') + 1
            );

            if (isset(self::SAFE_FILE_EXTENSIONS[$fileExtension])) {
                return $fileExtension;
            }
        }

        return 'bin';
    }
}
