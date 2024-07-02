<?php

namespace wcf\util;

use GuzzleHttp\Psr7\Header;
use wcf\system\exception\SystemException;
use wcf\system\image\ImageHandler;

/**
 * Contains image-related functions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class ImageUtil
{
    /**
     * image extensions
     *
     * @var string[]
     */
    public const IMAGE_EXTENSIONS = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

    /**
     * Checks the content of an image for bad sections, e.g. the use of javascript
     * and returns false if any bad stuff was found.
     *
     * @param string $file
     * @return  bool
     */
    public static function checkImageContent($file)
    {
        // get file content
        $content = \file_get_contents($file);

        // remove some characters
        $content = \strtolower(\preg_replace('/[^a-z0-9<\(]+/i', '', $content));
        $content = \str_replace('description', '', $content);

        // search for javascript
        if (
            \strpos($content, 'script') !== false
            || \strpos($content, 'javascript') !== false
            || \strpos($content, 'expression(') !== false
        ) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether a given file is a valid image.
     *
     * @param string $location
     * @param string|null $filename
     * @param bool $handleSvgAsValidImage flag, whether a svg file is handled as image
     * @return      bool
     */
    public static function isImage($location, $filename = null, $handleSvgAsValidImage = false)
    {
        if ($filename === null) {
            $filename = \basename($location);
        }

        if (@\getimagesize($location) !== false) {
            $extension = \pathinfo($filename, \PATHINFO_EXTENSION);

            if (\in_array(\mb_strtolower($extension), ImageUtil::IMAGE_EXTENSIONS)) {
                return true;
            }
        } elseif ($handleSvgAsValidImage) {
            if (
                \in_array(FileUtil::getMimeType($location), ['image/svg', 'image/svg+xml'])
                && \mb_strtolower(\pathinfo($filename, \PATHINFO_EXTENSION)) === 'svg'
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the file extension for an image with the given mime type.
     *
     * @param string $mimeType
     * @return  string
     * @see http://www.php.net/manual/en/function.image-type-to-mime-type.php
     */
    public static function getExtensionByMimeType($mimeType)
    {
        switch ($mimeType) {
            case 'image/gif':
                return 'gif';
            case 'image/jpeg':
                return 'jpg';
            case 'image/png':
                return 'png';
            case 'application/x-shockwave-flash':
                return 'swf';
            case 'image/psd':
                return 'psd';
            case 'image/bmp':
            case 'image/x-ms-bmp':
                return 'bmp';
            case 'image/tiff':
                return 'tiff';
            case 'image/webp':
                return 'webp';
            default:
                return '';
        }
    }

    /**
     * Enforces dimensions for given image.
     *
     * @param string $filename
     * @param int $maxWidth
     * @param int $maxHeight
     * @param bool $obtainDimensions
     * @return  string          new filename if file was changed, otherwise old filename
     * @since       5.2
     */
    public static function enforceDimensions($filename, $maxWidth, $maxHeight, $obtainDimensions = true)
    {
        $imageData = \getimagesize($filename);
        if ($imageData[0] > $maxWidth || $imageData[1] > $maxHeight) {
            $adapter = ImageHandler::getInstance()->getAdapter();
            $adapter->loadFile($filename);
            $filename = FileUtil::getTemporaryFilename();
            $thumbnail = $adapter->createThumbnail($maxWidth, $maxHeight, $obtainDimensions);
            $adapter->writeImage($thumbnail, $filename);
            // Clear thumbnail as soon as possible to free up the memory.
            // This is technically useless, but done for consistency.
            $thumbnail = null;
        }

        return $filename;
    }

    /**
     * Rotates the given image based on the orientation stored in the exif data.
     *
     * @param string $filename
     * @return  string          new filename if file was changed, otherwise old filename
     * @since       5.2
     */
    public static function fixOrientation($filename)
    {
        try {
            $exifData = ExifUtil::getExifData($filename);
            if (!empty($exifData)) {
                $orientation = ExifUtil::getOrientation($exifData);
                if ($orientation != ExifUtil::ORIENTATION_ORIGINAL) {
                    $adapter = ImageHandler::getInstance()->getAdapter();
                    $adapter->loadFile($filename);

                    $newImage = null;
                    switch ($orientation) {
                        case ExifUtil::ORIENTATION_180_ROTATE:
                            $newImage = $adapter->rotate(180);
                            break;

                        case ExifUtil::ORIENTATION_90_ROTATE:
                            $newImage = $adapter->rotate(90);
                            break;

                        case ExifUtil::ORIENTATION_270_ROTATE:
                            $newImage = $adapter->rotate(270);
                            break;

                        case ExifUtil::ORIENTATION_HORIZONTAL_FLIP:
                        case ExifUtil::ORIENTATION_VERTICAL_FLIP:
                        case ExifUtil::ORIENTATION_VERTICAL_FLIP_270_ROTATE:
                        case ExifUtil::ORIENTATION_HORIZONTAL_FLIP_270_ROTATE:
                            // unsupported
                            break;
                    }

                    if ($newImage !== null) {
                        if ($newImage instanceof \Imagick) {
                            $newImage->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
                        }

                        $adapter->load($newImage, $adapter->getType());
                    }

                    $newFilename = FileUtil::getTemporaryFilename();
                    $adapter->writeImage($newFilename);
                    $filename = $newFilename;
                }
            }
        } catch (SystemException $e) {
        }

        return $filename;
    }

    /**
     * Examines the `accept` header to determine if the browser
     * supports WebP images.
     *
     * @since 5.4
     */
    public static function browserSupportsWebp(): bool
    {
        static $supportsWebP = null;

        if ($supportsWebP === null) {
            $supportsWebP = false;
            if (!empty($_SERVER["HTTP_ACCEPT"])) {
                $acceptableMimeTypes = \array_map(static function ($acceptableMimeType) {
                    [$mimeType] = ArrayUtil::trim(\explode(";", $acceptableMimeType), false);

                    return $mimeType;
                }, Header::splitList($_SERVER["HTTP_ACCEPT"]));

                if (\in_array("image/webp", $acceptableMimeTypes)) {
                    $supportsWebP = true;
                }
            }
        }

        return $supportsWebP;
    }

    /**
     * Creates a WebP variant of the source image. Returns `true` if a
     * `webp` file was created, `false` if a jpeg was created and `null`
     * if no action was taken.
     */
    public static function createWebpVariant(string $sourceLocation, string $outputFilenameWithoutExtension): ?bool
    {
        $imageData = \getimagesize($sourceLocation);
        if ($imageData === false) {
            throw new \InvalidArgumentException("The source location is not a valid image.");
        }

        $extension = self::getExtensionByMimeType($imageData['mime']);
        switch ($extension) {
            case 'gif':
                // GIFs are not processed.
                return null;

            case 'jpg':
            case 'png':
            case 'webp':
                break;

            default:
                throw new \InvalidArgumentException(\sprintf(
                    "Unsupported image format '%s', expecting one of 'gif', 'jpg', 'png' or 'webp'.",
                    $extension
                ));
        }

        $imageAdapter = ImageHandler::getInstance()->getAdapter();
        $imageAdapter->loadFile($sourceLocation);
        $image = $imageAdapter->getImage();

        // The source file is a webp, create a fallback jpeg instead.
        if ($imageData[2] === \IMAGETYPE_WEBP) {
            $imageAdapter->saveImageAs($image, "{$outputFilenameWithoutExtension}.jpg", "jpeg", 80);

            return false;
        } else {
            $imageAdapter->saveImageAs($image, "{$outputFilenameWithoutExtension}.webp", "webp", 80);

            return true;
        }
    }

    /**
     * Forbid creation of ImageUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
