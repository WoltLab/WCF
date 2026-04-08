<?php

namespace wcf\system\file\upload;

use Laminas\Diactoros\Uri;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * An specific upload file.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class UploadFile
{
    /**
     * Location for the file.
     * @var string
     */
    private $location;

    /**
     * Full image link.
     * @var string
     */
    private $imageLink;

    /**
     * Flag whether svg files should detected as image
     * @var bool
     */
    private $detectSvgAsImage;

    /**
     * Indicator, whether the file is already processed.
     * @var bool
     */
    private $processed;

    /**
     * The filename.
     * @var string
     */
    private $filename;

    /**
     * Indicator, whether the file is an image.
     * @var bool
     */
    private $isImage;

    /**
     * Indicator, whether the file can be displayed as an image.
     * @var bool
     */
    public $viewableImage;

    /**
     * The filesize of the file.
     * @var int
     */
    public $filesize;

    /**
     * The unique id for the file.
     * @var string
     */
    private $uniqueId;

    /**
     * The return value of `getimagesize`.
     * @var array{0: int, 1: int, 2: int, 3: string, bits: int, channels: int, mime: string}|false
     */
    private $imageData;

    public function __construct(
        string $location,
        string $filename,
        bool $viewableImage = true,
        bool $processed = false,
        bool $detectSvgAsImage = false
    ) {
        if (!\file_exists($location)) {
            throw new \InvalidArgumentException("File '" . $location . "' could not be found.");
        }

        $this->location = $location;
        $this->filename = $filename;
        $this->filesize = \filesize($location);
        $this->processed = $processed;
        $this->viewableImage = $viewableImage;
        $this->uniqueId = StringUtil::getRandomID();
        $this->detectSvgAsImage = $detectSvgAsImage;
        $this->imageData = @\getimagesize($location);

        if (
            $this->imageData !== false || ($detectSvgAsImage && \in_array(FileUtil::getMimeType($location), [
                'image/svg',
                'image/svg+xml',
            ]))
        ) {
            $this->isImage = true;
        }
    }

    /**
     * Returns true, whether this file is an image.
     *
     * @return bool
     */
    public function isImage()
    {
        return $this->isImage;
    }

    /**
     * Returns the image location or a base64 encoded string of the image. Returns null
     * if the file is not an image or the image is not viewable.
     *
     * @return string|null
     */
    public function getImage()
    {
        if (!$this->isImage() || !$this->viewableImage) {
            return null;
        }

        if ($this->processed) {
            if ($this->imageLink === null) {
                $corePath = FileUtil::unifyDirSeparator(\WCF_DIR);
                $location = FileUtil::unifyDirSeparator($this->location);

                if (\str_starts_with($location, $corePath)) {
                    $link = WCF::getPath() . \mb_substr($location, \mb_strlen($corePath));
                } else {
                    $link = $location;
                }
            } else {
                $link = $this->imageLink;
            }

            $url = new Uri($link);
            $query = $url->getQuery();
            $cacheBuster = 't=' . \TIME_NOW;
            if ($query) {
                $url = $url->withQuery("{$query}&{$cacheBuster}");
            } else {
                $url = $url->withQuery($cacheBuster);
            }

            return (string)$url;
        } else {
            if ($this->imageData !== false) {
                return 'data:' . $this->imageData['mime'] . ';base64,' . \base64_encode(\file_get_contents($this->location));
            }

            if (
                $this->detectSvgAsImage && \in_array(FileUtil::getMimeType($this->location), [
                    'image/svg',
                    'image/svg+xml',
                ])
            ) {
                return 'data:image/svg+xml;base64,' . \base64_encode(\file_get_contents($this->location));
            }

            throw new \LogicException('File is an image, but can not be rendered.');
        }
    }

    /**
     * Returns the location of the file.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Returns the filename of the file.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Returns the file extension for the original filename. The returned string can be empty, if the
     * original filename has no file extension.
     *
     * @since   5.4
     */
    public function getFilenameExtension(): string
    {
        return \pathinfo($this->filename, \PATHINFO_EXTENSION);
    }

    /**
     * Returns the unique file id for the file. It is used to identify the certain file.
     * @return string
     */
    public function getUniqueFileId()
    {
        return $this->uniqueId;
    }

    /**
     * Sets the new location of the file, after it is processed and
     * sets the `processed` attribute to true.
     *
     * @return void
     */
    public function setProcessed(string $newLocation)
    {
        if (!\file_exists($newLocation)) {
            throw new \InvalidArgumentException("File '" . $newLocation . "' could not be found.");
        }

        $this->location = $newLocation;
        $this->processed = true;
    }

    /**
     * Sets the new image link of the file for processed files.
     *
     * @return void
     */
    public function setImageLink(string $link)
    {
        $this->imageLink = $link;
    }

    /**
     * Returns true, if the file is already processed.
     *
     * @return bool
     */
    public function isProcessed()
    {
        return $this->processed;
    }

    /**
     * @since 5.5
     */
    public function getWidth(): ?int
    {
        if ($this->imageData === false) {
            return null;
        }

        return $this->imageData[0];
    }

    /**
     * @since 5.5
     */
    public function getHeight(): ?int
    {
        if ($this->imageData === false) {
            return null;
        }

        return $this->imageData[1];
    }

    /**
     * Returns icon name for this attachment.
     */
    public function getIconName(): string
    {
        if ($iconName = FileUtil::getIconNameByFilename($this->filename)) {
            return \sprintf('file-%s', $iconName);
        }

        return 'paperclip';
    }
}
