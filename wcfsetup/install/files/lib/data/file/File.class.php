<?php

namespace wcf\data\file;

use wcf\action\FileDownloadAction;
use wcf\data\DatabaseObject;
use wcf\data\file\thumbnail\FileThumbnail;
use wcf\data\ILinkableObject;
use wcf\system\application\ApplicationHandler;
use wcf\system\file\processor\FileProcessor;
use wcf\system\file\processor\IFileProcessor;
use wcf\system\request\LinkHandler;
use wcf\util\JSON;
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
 * @property-read int|null $objectTypeID
 * @property-read string $mimeType
 * @property-read int|null $width
 * @property-read int|null $height
 * @property-read string|null $fileHashWebp
 */
class File extends DatabaseObject implements ILinkableObject
{
    /**
     * List of common file extensions that are always safe to be served directly
     * by the webserver.
     *
     * @var array<string, string>
     */
    public const SAFE_FILE_EXTENSIONS = [
        'gif' => 'image/gif',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];

    /** @var array<string, FileThumbnail> */
    private array $thumbnails = [];

    public function getSourceFilename(): string
    {
        return \sprintf(
            '%d-%s.%s',
            $this->fileID,
            $this->fileHash,
            $this->fileExtension,
        );
    }

    public function getSourceFilenameWebp(): ?string
    {
        if ($this->fileHashWebp === null) {
            return null;
        }

        // The filename uses the hash of the source file in order to keep the
        // source and the variant next to each. At the same time we do not
        // include the hash of the WebP variant in the filename because it would
        // yield an excessive filename, just the two hashes are 128 characters
        // in total.
        //
        // These variants are also a bit different because they are volatile and
        // can be regenerated from the source file at any time. The database is
        // the source of truth anyway thus we can safely discard files if they
        // do not match our expectations.
        return \sprintf(
            '%d-%s-variant.webp',
            $this->fileID,
            $this->fileHash,
        );
    }

    private function getRelativePath(): string
    {
        $folderA = \substr($this->fileHash, 0, 2);
        $folderB = \substr($this->fileHash, 2, 2);

        return \sprintf(
            '_data/%s/files/%s/%s/',
            $this->isStaticFile() ? 'public' : 'private',
            $folderA,
            $folderB,
        );
    }

    public function getPath(): string
    {
        return \WCF_DIR . $this->getRelativePath();
    }

    public function getPathname(): string
    {
        return $this->getPath() . $this->getSourceFilename();
    }

    public function getPathnameWebp(): ?string
    {
        $filename = $this->getSourceFilenameWebp();
        if ($filename === null) {
            return null;
        }

        return $this->getPath() . $filename;
    }

    #[\Override]
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getControllerLink(
            FileDownloadAction::class,
            [
                'id' => $this->fileID,
                'forceFrontend' => true,
            ]
        );
    }

    public function getFullSizeImageSource(): ?string
    {
        if (!$this->isImage() || !$this->isStaticFile()) {
            return null;
        }

        $filename = $this->getSourceFilenameWebp() ?: $this->getSourceFilename();

        return ApplicationHandler::getInstance()->getWCF()->getPageURL() . $this->getRelativePath() . $filename;
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

    public function isStaticFile(): bool
    {
        return $this->fileExtension !== 'bin';
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

    /**
     * @return array<string, FileThumbnail>
     */
    public function getThumbnails(): array
    {
        return $this->thumbnails;
    }

    public function toHtmlElement(?array $metaData = null): string
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
                    %s
                    data-link="%s"
                ></woltlab-core-file>
                EOT,
            $this->fileID,
            StringUtil::encodeHTML($this->filename),
            $this->fileSize,
            StringUtil::encodeHTML($this->mimeType),
            StringUtil::encodeHTML(JSON::encode($thumbnails)),
            $metaData === null ? "" : 'data-meta-data="' . StringUtil::encodeHTML(JSON::encode($metaData)) . '"',
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
            $fileExtension = \pathinfo($filename, \PATHINFO_EXTENSION);
            if (isset(self::SAFE_FILE_EXTENSIONS[$fileExtension])) {
                return $fileExtension;
            }
        }

        return 'bin';
    }
}
