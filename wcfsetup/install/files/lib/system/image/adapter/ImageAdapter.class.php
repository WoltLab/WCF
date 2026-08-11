<?php

namespace wcf\system\image\adapter;

use wcf\system\exception\SystemException;
use wcf\system\image\adapter\exception\ImageNotReadable;
use wcf\util\FileUtil;

/**
 * Wrapper for image adapters.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @implements IImageAdapter<\stdClass>
 * @implements IMemoryAwareImageAdapter<\stdClass>
 */
class ImageAdapter implements IImageAdapter, IMemoryAwareImageAdapter, ISingleFrameImageAdapter
{
    /**
     * IImageAdapter object
     * @var IImageAdapter<\stdClass>
     */
    protected $adapter;

    /**
     * supported relative positions
     * @var string[]
     */
    protected $relativePositions = [
        'topLeft',
        'topCenter',
        'topRight',
        'middleLeft',
        'middleCenter',
        'middleRight',
        'bottomLeft',
        'bottomCenter',
        'bottomRight',
    ];

    public function __construct(string $adapterClassName)
    {
        $this->adapter = new $adapterClassName();
    }

    #[\Override]
    public function load(object $image, int $type = 0)
    {
        $this->adapter->load($image, $type);
    }

    #[\Override]
    public function loadFile(string $file)
    {
        if (!\file_exists($file) || !\is_readable($file)) {
            throw new SystemException("Image '" . $file . "' is not readable or does not exists.");
        }

        $this->adapter->loadFile($file);
    }

    #[\Override]
    public function loadSingleFrameFromFile(string $filename): void
    {
        if (!($this->adapter instanceof ISingleFrameImageAdapter)) {
            $this->adapter->loadFile($filename);

            return;
        }

        if (!\file_exists($filename) || !\is_readable($filename)) {
            throw new ImageNotReadable($filename);
        }

        $this->adapter->loadSingleFrameFromFile($filename);
    }

    #[\Override]
    public function createEmptyImage(int $width, int $height)
    {
        $this->adapter->createEmptyImage($width, $height);
    }

    #[\Override]
    public function createThumbnail(int $maxWidth, int $maxHeight, bool $preserveAspectRatio = true)
    {
        if ($maxWidth > $this->getWidth() && $maxHeight > $this->getHeight()) {
            throw new SystemException(
                \sprintf(
                    "Dimensions for thumbnail can not exceed image dimensions (requested: %d × %d, actual: %d × %d).",
                    $maxWidth,
                    $maxHeight,
                    $this->getWidth(),
                    $this->getHeight(),
                )
            );
        }

        $maxHeight = \min($maxHeight, $this->getHeight());
        $maxWidth = \min($maxWidth, $this->getWidth());

        return $this->adapter->createThumbnail($maxWidth, $maxHeight, $preserveAspectRatio);
    }

    #[\Override]
    public function clip(int $originX, int $originY, int $width, int $height)
    {
        // validate if coordinates and size are within bounds
        if ($originX < 0 || $originY < 0) {
            throw new SystemException("Clipping an image requires valid offsets, an offset below zero is invalid.");
        }
        if ($width <= 0 || $height <= 0) {
            throw new SystemException(
                "Clipping an image requires valid dimensions, width or height below or equal zero are invalid."
            );
        }
        if ((($originX + $width) > $this->getWidth()) || (($originY + $height) > $this->getHeight())) {
            throw new SystemException("Offset and dimension can not exceed image dimensions.");
        }

        $this->adapter->clip($originX, $originY, $width, $height);
    }

    #[\Override]
    public function resize(int $originX, int $originY, int $originWidth, int $originHeight, int $targetWidth, int $targetHeight)
    {
        // use origin dimensions if target dimensions are both zero
        if ($targetWidth === 0 && $targetHeight === 0) {
            $targetWidth = $originWidth;
            $targetHeight = $originHeight;
        }

        $this->adapter->resize($originX, $originY, $originWidth, $originHeight, $targetWidth, $targetHeight);
    }

    #[\Override]
    public function drawRectangle(int $startX, int $startY, int $endX, int $endY)
    {
        if (!$this->adapter->hasColor()) {
            throw new SystemException("Cannot draw a rectangle unless a color has been specified with setColor().");
        }

        $this->adapter->drawRectangle($startX, $startY, $endX, $endY);
    }

    #[\Override]
    public function drawText(string $text, int $x, int $y, string $font, int $size, float $opacity = 1.0)
    {
        if (!$this->adapter->hasColor()) {
            throw new SystemException("Cannot draw text unless a color has been specified with setColor().");
        }

        // validate opacity
        if ($opacity < 0 || $opacity > 1) {
            throw new SystemException("Invalid opacity value given.");
        }

        $this->adapter->drawText($text, $x, $y, $font, $size, $opacity);
    }

    #[\Override]
    public function drawTextRelative(string $text, string $position, int $margin, int $offsetX, int $offsetY, string $font, int $size, float $opacity = 1.0)
    {
        if (!$this->adapter->hasColor()) {
            throw new SystemException("Cannot draw text unless a color has been specified with setColor().");
        }

        // validate position
        if (!\in_array($position, $this->relativePositions, true)) {
            throw new SystemException("Unknown relative position '" . $position . "'.");
        }

        // validate margin
        if ($margin < 0 || $margin >= $this->getHeight() / 2 || $margin >= $this->getWidth() / 2) {
            throw new SystemException("Margin has to be positive and respect image dimensions.");
        }

        // validate opacity
        if ($opacity < 0 || $opacity > 1) {
            throw new SystemException("Invalid opacity value given.");
        }

        $this->adapter->drawTextRelative($text, $position, $margin, $offsetX, $offsetY, $font, $size, $opacity);
    }

    #[\Override]
    public function textFitsImage(string $text, int $margin, string $font, int $size)
    {
        return $this->adapter->textFitsImage($text, $margin, $font, $size);
    }

    #[\Override]
    public function adjustFontSize(string $text, int $margin, string $font, int $size)
    {
        // adjust font size
        while ($size !== 0 && !$this->textFitsImage($text, $margin, $font, $size)) {
            $size--;
        }

        return $size;
    }

    #[\Override]
    public function setColor(int $red, int $green, int $blue)
    {
        $this->adapter->setColor($red, $green, $blue);
    }

    #[\Override]
    public function hasColor()
    {
        return $this->adapter->hasColor();
    }

    #[\Override]
    public function setTransparentColor(int $red, int $green, int $blue)
    {
        $this->adapter->setTransparentColor($red, $green, $blue);
    }

    #[\Override]
    public function writeImage(object|string $image, ?string $filename = null)
    {
        if ($filename === null) {
            $filename = $image;
            $image = $this->adapter->getImage();
        }

        $this->adapter->writeImage($image, $filename);
    }

    #[\Override]
    public function getImage()
    {
        return $this->adapter->getImage();
    }

    #[\Override]
    public function getWidth()
    {
        return $this->adapter->getWidth();
    }

    #[\Override]
    public function getHeight()
    {
        return $this->adapter->getHeight();
    }

    #[\Override]
    public function getType()
    {
        return $this->adapter->getType();
    }

    #[\Override]
    public function rotate(float $degrees)
    {
        if ($degrees > 360.0 || $degrees < 0.0) {
            throw new SystemException("Degrees must be a value between 0 and 360.");
        }

        return $this->adapter->rotate($degrees);
    }

    #[\Override]
    public function overlayImage(string $file, int $x, int $y, float $opacity)
    {
        // validate file
        if (!\file_exists($file)) {
            throw new SystemException("Image '" . $file . "' does not exist.");
        }

        // validate opacity
        if ($opacity < 0 || $opacity > 1) {
            throw new SystemException("Invalid opacity value given.");
        }

        $this->adapter->overlayImage($file, $x, $y, $opacity);
    }

    #[\Override]
    public function overlayImageRelative(string $file, string $position, int $margin, float $opacity)
    {
        // validate file
        if (!\file_exists($file)) {
            throw new SystemException("Image '" . $file . "' does not exist.");
        }

        // validate position
        if (!\in_array($position, $this->relativePositions, true)) {
            throw new SystemException("Unknown relative position '" . $position . "'.");
        }

        // validate margin
        if ($margin < 0 || $margin >= $this->getHeight() / 2 || $margin >= $this->getWidth() / 2) {
            throw new SystemException("Margin has to be positive and respect image dimensions.");
        }

        // validate opacity
        if ($opacity < 0 || $opacity > 1) {
            throw new SystemException("Invalid opacity value given.");
        }

        $adapterClassName = \get_class($this->adapter);

        /** @var IImageAdapter<\stdClass> $overlayImage */
        $overlayImage = new $adapterClassName();
        $overlayImage->loadFile($file);
        $overlayHeight = $overlayImage->getHeight();
        $overlayWidth = $overlayImage->getWidth();

        // calculate y coordinate
        $x = 0;
        switch ($position) {
            case 'topLeft':
            case 'middleLeft':
            case 'bottomLeft':
                $x = $margin;
                break;

            case 'topCenter':
            case 'middleCenter':
            case 'bottomCenter':
                $x = \floor(($this->getWidth() - $overlayWidth) / 2);
                break;

            case 'topRight':
            case 'middleRight':
            case 'bottomRight':
                $x = $this->getWidth() - $overlayWidth - $margin;
                break;
        }

        // calculate y coordinate
        $y = 0;
        switch ($position) {
            case 'topLeft':
            case 'topCenter':
            case 'topRight':
                $y = $margin;
                break;

            case 'middleLeft':
            case 'middleCenter':
            case 'middleRight':
                $y = \floor(($this->getHeight() - $overlayHeight) / 2);
                break;

            case 'bottomLeft':
            case 'bottomCenter':
            case 'bottomRight':
                $y = $this->getHeight() - $overlayHeight - $margin;
                break;
        }

        $this->overlayImage($file, $x, $y, $opacity);
    }

    #[\Override]
    public function checkMemoryLimit(int $width, int $height, string $mimeType)
    {
        if ($this->adapter instanceof IMemoryAwareImageAdapter) {
            return $this->adapter->checkMemoryLimit($width, $height, $mimeType);
        }

        $channels = $mimeType === 'image/png' ? 4 : 3;

        return FileUtil::checkMemoryLimit((int)($width * $height * $channels * 2.1));
    }

    #[\Override]
    public function saveImageAs(object $image, string $filename, string $type, int $quality = 100): void
    {
        switch ($type) {
            case "gif":
            case "jpg":
            case "jpeg":
            case "png":
            case "webp":
                break;

            default:
                throw new \InvalidArgumentException("Unsupported image format '{$type}'.");
        }

        if ($quality < 0 || $quality > 100) {
            throw new \InvalidArgumentException("The quality must be an integer between 0 and 100.");
        }

        $this->adapter->saveImageAs($image, $filename, $type, $quality);
    }

    #[\Override]
    public static function isSupported()
    {
        return false;
    }
}
