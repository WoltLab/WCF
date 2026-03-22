<?php

namespace wcf\system\image\adapter;

/**
 * Basic interface for all image adapters.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @template T of object
 */
interface IImageAdapter
{
    /**
     * Loads an image resource.
     *
     * @param T $image
     * @return void
     */
    public function load(object $image, int $type = 0);

    /**
     * Loads an image from file.
     *
     * @return void
     */
    public function loadFile(string $file);

    /**
     * Creates a new empty image.
     *
     * @return void
     */
    public function createEmptyImage(int $width, int $height);

    /**
     * Creates a thumbnail from previously loaded image.
     *
     * @return mixed
     */
    public function createThumbnail(int $maxWidth, int $maxHeight, bool $preserveAspectRatio = true);

    /**
     * Clips a part of currently loaded image, overwrites image resource within instance.
     *
     * @return void
     * @see \wcf\system\image\adapter\IImageAdapter::getImage()
     */
    public function clip(int $originX, int $originY, int $width, int $height);

    /**
     * Resizes an image with optional scaling, overwrites image resource within instance.
     *
     * @return void
     * @see \wcf\system\image\adapter\IImageAdapter::getImage()
     */
    public function resize(int $originX, int $originY, int $originWidth, int $originHeight, int $targetWidth, int $targetHeight);

    /**
     * Draws a rectangle, overwrites image resource within instance.
     *
     * @return void
     * @see \wcf\system\image\adapter\IImageAdapter::getImage()
     * @see \wcf\system\image\adapter\IImageAdapter::setColor()
     */
    public function drawRectangle(int $startX, int $startY, int $endX, int $endY);

    /**
     * Draws a line of text, overwrites image resource within instance.
     *
     * @param string $font path to TrueType font file
     * @return void
     * @see \wcf\system\image\adapter\IImageAdapter::getImage()
     * @see \wcf\system\image\adapter\IImageAdapter::setColor()
     */
    public function drawText(string $text, int $x, int $y, string $font, int $size, float $opacity = 1.0);

    /**
     * Draws (multiple lines of) text on the image at the given relative position
     * with a certain margin to the image border.
     *
     * @param string $font path to TrueType font file
     * @return void
     */
    public function drawTextRelative(string $text, string $position, int $margin, int $offsetX, int $offsetY, string $font, int $size, float $opacity = 1.0);

    /**
     * Returns true if the given text fits the image.
     *
     * @param string $font path to TrueType font file
     * @return bool
     */
    public function textFitsImage(string $text, int $margin, string $font, int $size);

    /**
     * Adjusts the given font size so that the given text fits on the current
     * image. Returns 0 if no appropriate font size could be determined.
     *
     * @param string $font path to TrueType font file
     * @return int
     */
    public function adjustFontSize(string $text, int $margin, string $font, int $size);

    /**
     * Sets active color.
     *
     * @return void
     */
    public function setColor(int $red, int $green, int $blue);

    /**
     * Returns true if a color has been set.
     *
     * @return bool
     */
    public function hasColor();

    /**
     * Sets a color to be transparent with alpha 0.
     *
     * @return void
     */
    public function setTransparentColor(int $red, int $green, int $blue);

    /**
     * Writes an image to disk.
     *
     * @param T|string $image
     * @return void
     */
    public function writeImage(object|string $image, ?string $filename);

    /**
     * Returns image resource.
     *
     * @return mixed
     */
    public function getImage();

    /**
     * Returns image width.
     *
     * @return int
     */
    public function getWidth();

    /**
     * Returns image height
     *
     * @return int
     */
    public function getHeight();

    /**
     * Returns the image type (GD only)
     *
     * @return int
     */
    public function getType();

    /**
     * Rotates an image the specified number of degrees.
     *
     * @param float $degrees number of degrees to rotate the image clockwise
     * @return  mixed
     */
    public function rotate(float $degrees);

    /**
     * Overlays the given image at an absolute position.
     *
     * @return void
     */
    public function overlayImage(string $file, int $x, int $y, float $opacity);

    /**
     * Overlays the given image at a relative position.
     *
     * @return void
     */
    public function overlayImageRelative(string $file, string $position, int $margin, float $opacity);

    /**
     * Saves an image using a different file type.
     *
     * @since 5.4
     */
    public function saveImageAs(object $image, string $filename, string $type, int $quality = 100): void;

    /**
     * Determines if an image adapter is supported.
     *
     * @return bool
     */
    public static function isSupported();
}
