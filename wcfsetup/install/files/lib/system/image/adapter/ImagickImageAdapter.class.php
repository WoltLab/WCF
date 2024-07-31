<?php

namespace wcf\system\image\adapter;

use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\image\adapter\exception\ImageNotProcessable;
use wcf\util\StringUtil;

/**
 * Image adapter for ImageMagick imaging library.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ImagickImageAdapter implements IImageAdapter, ISingleFrameImageAdapter, IWebpImageAdapter
{
    /**
     * active color
     * @var \ImagickPixel
     */
    protected $color;

    /**
     * Imagick object
     * @var \Imagick
     */
    protected $imagick;

    /**
     * image height
     * @var int
     */
    protected $height = 0;

    /**
     * image width
     * @var int
     */
    protected $width = 0;

    /**
     * is true if the used configuration can write animated GIF files if the
     * PHP Imagick API version is 3.1.0 RC 1
     * @var bool
     */
    protected $supportsWritingAnimatedGIF = true;

    /**
     * List of image format that support animations.
     */
    protected static array $animatedFormats = ['GIF', 'WEBP'];

    /**
     * Creates a new ImagickImageAdapter.
     */
    public function __construct()
    {
        $this->imagick = new \Imagick();

        if (!static::supportsAnimatedGIFs(static::getVersion())) {
            $this->supportsWritingAnimatedGIF = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function load($image, $type = '')
    {
        if (!($image instanceof \Imagick)) {
            throw new SystemException("Object must be an instance of Imagick");
        }

        $this->imagick = $image;

        $this->readImageDimensions();
    }

    /**
     * @inheritDoc
     */
    public function loadFile($file)
    {
        try {
            $this->imagick->clear();
            $this->imagick->readImage($file);
        } catch (\ImagickException $e) {
            throw new SystemException("Image '" . $file . "' is not readable or does not exist.", 0, '', $e);
        }

        $this->readImageDimensions();
    }

    #[\Override]
    public function loadSingleFrameFromFile(string $filename): void
    {
        try {
            $this->imagick->clear();
            $this->imagick->readImage($filename . '[0]');
        } catch (\ImagickException $e) {
            throw new ImageNotProcessable($filename, $e);
        }

        $this->readImageDimensions();
    }

    /**
     * Reads width and height of the image.
     */
    protected function readImageDimensions()
    {
        // fix height/width for animated gifs as getImageHeight/getImageWidth
        // returns the height/width of ONE frame of the animated image,
        // not the "real" height/width of the image
        if (\in_array($this->imagick->getImageFormat(), self::$animatedFormats)) {
            $imagick = $this->imagick->coalesceImages();

            $this->height = $imagick->getImageHeight();
            $this->width = $imagick->getImageWidth();

            $imagick->clear();
        } else {
            $this->height = $this->imagick->getImageHeight();
            $this->width = $this->imagick->getImageWidth();
        }
    }

    /**
     * @inheritDoc
     */
    public function createEmptyImage($width, $height)
    {
        $this->imagick->newImage($width, $height, 'white');

        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @inheritDoc
     */
    public function createThumbnail($maxWidth, $maxHeight, $preserveAspectRatio = true)
    {
        $maxHeight = (int)$maxHeight;
        $maxWidth = (int)$maxWidth;

        $thumbnail = clone $this->imagick;

        if (\in_array($thumbnail->getImageFormat(), self::$animatedFormats)) {
            $thumbnail = $thumbnail->coalesceImages();

            do {
                if ($preserveAspectRatio) {
                    $thumbnail->thumbnailImage($maxWidth, $maxHeight, true);
                    $thumbnail->setImagePage(0, 0, 0, 0);
                } else {
                    $thumbnail->cropThumbnailImage($maxWidth, $maxHeight);
                    $thumbnail->setImagePage($maxWidth, $maxHeight, 0, 0);
                }
            } while ($thumbnail->nextImage());
        } elseif ($preserveAspectRatio) {
            $thumbnail->thumbnailImage($maxWidth, $maxHeight, true);
        } else {
            $thumbnail->cropThumbnailImage($maxWidth, $maxHeight);
        }

        return $thumbnail;
    }

    /**
     * @inheritDoc
     */
    public function clip($originX, $originY, $width, $height)
    {
        if (\in_array($this->imagick->getImageFormat(), self::$animatedFormats)) {
            $this->imagick = $this->imagick->coalesceImages();

            do {
                $this->imagick->cropImage($width, $height, $originX, $originY);
                $this->imagick->setImagePage($width, $height, 0, 0);
            } while ($this->imagick->nextImage());
        } else {
            $this->imagick->cropImage($width, $height, $originX, $originY);
        }
    }

    /**
     * @inheritDoc
     */
    public function resize($originX, $originY, $originWidth, $originHeight, $targetWidth, $targetHeight)
    {
        if (\in_array($this->imagick->getImageFormat(), self::$animatedFormats)) {
            $image = $this->imagick->coalesceImages();

            foreach ($image as $frame) {
                $frame->cropImage($originWidth, $originHeight, $originX, $originY);
                $frame->thumbnailImage($targetWidth, $targetHeight);
                $frame->setImagePage($targetWidth, $targetHeight, 0, 0);
            }

            $this->imagick = $image->deconstructImages();
        } else {
            $this->clip($originX, $originY, $originWidth, $originHeight);

            $this->imagick->resizeImage($targetWidth, $targetHeight, $this->getResizeFilter(), 0);
        }
    }

    /**
     * @inheritDoc
     */
    public function drawRectangle($startX, $startY, $endX, $endY)
    {
        $draw = new \ImagickDraw();
        $draw->setFillColor($this->color);
        $draw->setStrokeColor($this->color);
        $draw->rectangle($startX, $startY, $endX, $endY);

        $this->imagick->drawImage($draw);
    }

    /**
     * @inheritDoc
     */
    public function drawText($text, $x, $y, $font, $size, $opacity = 1.0)
    {
        $draw = new \ImagickDraw();
        $draw->setFillColor($this->color);
        $draw->setFillOpacity($opacity);
        $draw->setTextAntialias(true);
        $draw->setFont($font);
        $draw->setFontSize($size * 4 / 3);

        // draw text
        $draw->annotation($x, $y, $text);

        if (\in_array($this->imagick->getImageFormat(), self::$animatedFormats)) {
            $this->imagick = $this->imagick->coalesceImages();

            do {
                $this->imagick->drawImage($draw);
            } while ($this->imagick->nextImage());
        } else {
            $this->imagick->drawImage($draw);
        }
    }

    /**
     * @inheritDoc
     */
    public function drawTextRelative($text, $position, $margin, $offsetX, $offsetY, $font, $size, $opacity = 1.0)
    {
        // split text into multiple lines
        $lines = \explode("\n", StringUtil::unifyNewlines($text));

        $draw = new \ImagickDraw();
        $draw->setFont($font);
        $draw->setFontSize($size * 4 / 3);
        $metrics = $this->imagick->queryFontMetrics($draw, $text);
        $textWidth = $metrics['textWidth'];
        $textHeight = $metrics['textHeight'];
        $firstLineMetrics = $this->imagick->queryFontMetrics($draw, $lines[0]);
        $firstLineHeight = $firstLineMetrics['textHeight'];

        // calculate x coordinate
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
                $x = \floor(($this->getWidth() - $textWidth) / 2);
                break;

            case 'topRight':
            case 'middleRight':
            case 'bottomRight':
                $x = $this->getWidth() - $textWidth - $margin;
                break;
        }

        // calculate y coordinate
        $y = 0;
        switch ($position) {
            case 'topLeft':
            case 'topCenter':
            case 'topRight':
                $y = $margin + $firstLineHeight;
                break;

            case 'middleLeft':
            case 'middleCenter':
            case 'middleRight':
                $y = \floor(($this->getHeight() - $textHeight) / 2) + $firstLineHeight;
                break;

            case 'bottomLeft':
            case 'bottomCenter':
            case 'bottomRight':
                $y = $this->getHeight() - $textHeight + $firstLineHeight - $margin;
                break;
        }

        // draw text
        $this->drawText($text, $x + $offsetX, $y + $offsetY, $font, $size, $opacity);
    }

    /**
     * @inheritDoc
     */
    public function textFitsImage($text, $margin, $font, $size)
    {
        $draw = new \ImagickDraw();
        $draw->setFont($font);
        $draw->setFontSize($size * 4 / 3);
        $metrics = $this->imagick->queryFontMetrics($draw, $text);

        return $metrics['textWidth'] + 2 * $margin <= $this->getWidth() && $metrics['textHeight'] + 2 * $margin <= $this->getHeight();
    }

    /**
     * @inheritDoc
     */
    public function adjustFontSize($text, $margin, $font, $size)
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    public function setColor($red, $green, $blue)
    {
        $this->color = new \ImagickPixel();
        $this->color->setColor('rgb(' . $red . ',' . $green . ',' . $blue . ')');
    }

    /**
     * @inheritDoc
     */
    public function hasColor()
    {
        if ($this->color instanceof \ImagickPixel) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function setTransparentColor($red, $green, $blue)
    {
        $color = 'rgb(' . $red . ',' . $green . ',' . $blue . ')';
        $this->imagick->paintTransparentImage($color, 0.0, 0);
    }

    /**
     * @inheritDoc
     */
    public function getImage()
    {
        return $this->imagick;
    }

    /**
     * @inheritDoc
     */
    public function writeImage($image, $filename)
    {
        if (!($image instanceof \Imagick)) {
            throw new SystemException("Given image is not a valid Imagick-object.");
        }

        // Greatly reduces the time required to create the image and drastically
        // reduces the filesize to more reasonable levels without a visible
        // quality loss.
        //
        // See https://github.com/Imagick/imagick/issues/360
        if ($image->getImageFormat() == 'GIF') {
            $image = $image->deconstructImages();
            $image->quantizeImages(256, \Imagick::COLORSPACE_SRGB, 0, false, false);
        }

        $image->writeImages($filename, true);
    }

    /**
     * @inheritDoc
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @inheritDoc
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function rotate($degrees)
    {
        $image = clone $this->imagick;
        $image->rotateImage(($this->color ?: new \ImagickPixel()), $degrees);

        return $image;
    }

    /**
     * @inheritDoc
     */
    public function overlayImage($file, $x, $y, $opacity)
    {
        try {
            $overlayImage = new \Imagick($file);
        } catch (\ImagickException $e) {
            throw new SystemException("Image '" . $file . "' is not readable or does not exist.", 0, '', $e);
        }

        // Explicitly enable transparency if the target image has transparent pixels,
        // otherwise the background color is replaced by #ffffff.
        if ($this->imagick->getImageAlphaChannel()) {
            $this->imagick->setImageBackgroundColor('transparent');
        }

        $overlayImage->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity, \Imagick::CHANNEL_OPACITY);

        if (\in_array($this->imagick->getImageFormat(), self::$animatedFormats)) {
            $this->imagick = $this->imagick->coalesceImages();

            do {
                $this->imagick->compositeImage($overlayImage, \Imagick::COMPOSITE_OVER, $x, $y);
            } while ($this->imagick->nextImage());
        } else {
            $this->imagick->compositeImage($overlayImage, \Imagick::COMPOSITE_OVER, $x, $y);
            $this->imagick = $this->imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        }
    }

    /**
     * @inheritDoc
     */
    public function overlayImageRelative($file, $position, $margin, $opacity)
    {
        // does nothing
    }

    /**
     * Returns the preferred image filter used during image resizing.
     *
     * @return int
     */
    protected function getResizeFilter()
    {
        static $filter;

        if ($filter === null) {
            $parameters = ['filter' => null];
            EventHandler::getInstance()->fireAction($this, 'getResizeFilter', $parameters);

            $filter = $parameters['filter'] ?? \Imagick::FILTER_POINT;
        }

        return $filter;
    }

    /**
     * @inheritDoc
     */
    public static function isSupported()
    {
        return \class_exists('\Imagick', false);
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        \preg_match('~(?P<version>[0-9]+\.[0-9]+\.[0-9]+)~', \Imagick::getVersion()['versionString'], $match);

        return $match['version'];
    }

    /**
     * @inheritDoc
     */
    public function saveImageAs($image, string $filename, string $type, int $quality = 100): void
    {
        if (!($image instanceof \Imagick)) {
            throw new \InvalidArgumentException("Given image is not a valid Imagick-object.");
        }

        $image->setImageCompressionQuality($quality);

        // Greatly reduces the time required to create the image and drastically
        // reduces the filesize to more reasonable levels without a visible
        // quality loss.
        //
        // See https://github.com/Imagick/imagick/issues/360
        if ($image->getImageFormat() == "GIF") {
            $image = $image->deconstructImages();
            $image->quantizeImages(256, \Imagick::COLORSPACE_SRGB, 0, false, false);
        }

        switch ($type) {
            case "jpg":
            case "jpeg":
                $fileFormat = "jpg";
                break;

            case "png":
                $fileFormat = "png";
                break;

            case "webp":
                $fileFormat = "webp";
                break;

            default:
                throw new \LogicException("Unreachable");
        }

        // When converting an animated WEBP to another format,
        // we need to make sure that only the 1st frame is used.
        // Otherwise Imagick will create a separate file for each frame.
        if ($image->getImageFormat() == 'WEBP' && $filename != 'webp') {
            $sourceImage = $image;
            $image = new \Imagick();
            foreach ($sourceImage as $frame) {
                $image->addImage($frame->getImage());
                break;
            }
        }

        $image->writeImages("{$fileFormat}:{$filename}", true);
    }

    /**
     * @param string $version
     * @return bool
     */
    public static function supportsAnimatedGIFs($version)
    {
        return \version_compare($version, '6.3.6') >= 0;
    }

    /**
     * @inheritDoc
     */
    public static function supportsWebp(): bool
    {
        return \in_array('WEBP', \Imagick::queryFormats());
    }
}
