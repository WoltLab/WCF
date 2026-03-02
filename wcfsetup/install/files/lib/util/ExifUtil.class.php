<?php

namespace wcf\util;

use WoltLab\WebpExif\Decoder;
use WoltLab\WebpExif\Encoder;
use WoltLab\WebpExif\Exception\WebpExifException;

/**
 * Provides exif-related functions.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class ExifUtil
{
    /**
     * orientation value for the original orientation
     * @see http://jpegclub.org/exif_orientation.html
     * @var int
     */
    const ORIENTATION_ORIGINAL = 1;

    /**
     * orientation value of a horizontal flip
     * @see http://jpegclub.org/exif_orientation.html
     * @var int
     */
    const ORIENTATION_HORIZONTAL_FLIP = 2;

    /**
     * orientation value of a 180 degree rotation
     * @see http://jpegclub.org/exif_orientation.html
     * @var int
     */
    const ORIENTATION_180_ROTATE = 3;

    /**
     * orientation value of a vertical flip
     * @see http://jpegclub.org/exif_orientation.html
     * @var int
     */
    const ORIENTATION_VERTICAL_FLIP = 4;

    /**
     * orientation value of a vertical flip and a 270 degree rotation
     * @see http://jpegclub.org/exif_orientation.html
     * @var int
     */
    const ORIENTATION_VERTICAL_FLIP_270_ROTATE = 5;

    /**
     * orientation value of a 90 degree rotation
     * @see http://jpegclub.org/exif_orientation.html
     * @var int
     */
    const ORIENTATION_90_ROTATE = 6;

    /**
     * orientation value of a horizontal flip and a 270 degree rotation
     * @see http://jpegclub.org/exif_orientation.html
     * @var int
     */
    const ORIENTATION_HORIZONTAL_FLIP_270_ROTATE = 7;

    /**
     * orientation value of a 270 degree rotation
     * @see http://jpegclub.org/exif_orientation.html
     * @var int
     */
    const ORIENTATION_270_ROTATE = 8;

    /**
     * Forbid creation of ExifUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }

    /**
     * Returns the exif data of the image at the given location or an empty
     * array if the exif data can't be read.
     *
     * @return mixed[]
     */
    public static function getExifData(string $filename): array
    {
        $mimeType = FileUtil::getMimeType($filename);
        if ($mimeType === 'image/webp') {
            $decoder = new Decoder();

            try {
                $webp = $decoder->fromBinary(\file_get_contents($filename));
            } catch (WebpExifException) {
                return [];
            }

            $exifData = $webp->getExif()?->getParsedExif();
            if ($exifData === null) {
                return [];
            }

            return $exifData;
        }

        $exifData = @\exif_read_data($filename, '', true);
        if ($exifData !== false) {
            return $exifData;
        }

        return [];
    }

    /**
     * Returns the name of the used camera based on the given exif data.
     *
     * @param mixed[] $exifData
     */
    public static function getCamera(array $exifData): string
    {
        $camera = '';
        if (isset($exifData['IFD0'])) {
            $maker = '';
            if (!empty($exifData['IFD0']['Make'])) {
                $maker = $exifData['IFD0']['Make'];
            }

            if (!empty($exifData['IFD0']['Model'])) {
                $camera = $exifData['IFD0']['Model'];
                if ($maker != '' && \strpos($camera, $maker) === false) {
                    $camera = $maker . ' ' . $camera;
                }
            }
        }

        return $camera;
    }

    /**
     * Returns the creation timestamp based on the given exif data.
     *
     * @param mixed[] $exifData
     */
    public static function getCreationTime(array $exifData): int
    {
        $creationTime = 0;
        if (isset($exifData['EXIF'])) {
            if (isset($exifData['EXIF']['DateTimeOriginal'])) {
                $creationTime = @\intval(\strtotime($exifData['EXIF']['DateTimeOriginal']));
            } elseif (isset($exifData['EXIF']['DateTimeDigitized'])) {
                $creationTime = @\intval(\strtotime($exifData['EXIF']['DateTimeDigitized']));
            } elseif (!empty($exifData['EXIF']['DateTime'])) {
                $creationTime = @\intval(\strtotime($exifData['EXIF']['DateTime']));
            }
        }
        if ($creationTime < 0 || $creationTime > 2147483647) {
            $creationTime = 0;
        }

        return $creationTime;
    }

    /**
     * Returns the longitude of the place the image with the given exif data
     * was taken.
     *
     * @param mixed[] $exifData
     */
    public static function getLongitude(array $exifData): float
    {
        $longitude = 0;
        if (isset($exifData['GPS']) && isset($exifData['GPS']['GPSLongitudeRef']) && isset($exifData['GPS']['GPSLongitude'])) {
            $degrees = (isset($exifData['GPS']['GPSLongitude'][0]) ? self::convertCoordinateToDecimal($exifData['GPS']['GPSLongitude'][0]) : 0.0);
            $minutes = (isset($exifData['GPS']['GPSLongitude'][1]) ? self::convertCoordinateToDecimal($exifData['GPS']['GPSLongitude'][1]) : 0.0);
            $seconds = (isset($exifData['GPS']['GPSLongitude'][2]) ? self::convertCoordinateToDecimal($exifData['GPS']['GPSLongitude'][2]) : 0.0);
            $longitude = ($degrees * 60.0 + (($minutes * 60.0 + $seconds) / 60.0)) / 60.0;
            if ($exifData['GPS']['GPSLongitudeRef'] == 'W') {
                $longitude *= -1;
            }
        }

        if ($longitude < -180.0 || $longitude > 180.0) {
            $longitude = 0;
        }

        return $longitude;
    }

    /**
     * Returns the latitude of the place the image with the given exif data
     * was taken.
     *
     * @param mixed[] $exifData
     */
    public static function getLatitude(array $exifData): int|float
    {
        $latitude = 0;
        if (isset($exifData['GPS']) && isset($exifData['GPS']['GPSLatitudeRef']) && isset($exifData['GPS']['GPSLatitude'])) {
            $degrees = isset($exifData['GPS']['GPSLatitude'][0]) ? self::convertCoordinateToDecimal($exifData['GPS']['GPSLatitude'][0]) : 0.0;
            $minutes = isset($exifData['GPS']['GPSLatitude'][1]) ? self::convertCoordinateToDecimal($exifData['GPS']['GPSLatitude'][1]) : 0.0;
            $seconds = isset($exifData['GPS']['GPSLatitude'][2]) ? self::convertCoordinateToDecimal($exifData['GPS']['GPSLatitude'][2]) : 0.0;
            $latitude = ($degrees * 60.0 + (($minutes * 60.0 + $seconds) / 60.0)) / 60.0;
            if ($exifData['GPS']['GPSLatitudeRef'] == 'S') {
                $latitude *= -1;
            }
        }

        if ($latitude < -90.0 || $latitude > 90.0) {
            $latitude = 0;
        }

        return $latitude;
    }

    /**
     * Returns the formats exif data.
     *
     * @param mixed[] $rawExifData
     * @return array<string, string|int|float>
     */
    public static function getFormattedExifData(array $rawExifData): array
    {
        $exifData = [];

        // unit is second (unsigned rational)
        if (isset($rawExifData['ExposureTime']) && \is_string($rawExifData['ExposureTime'])) {
            $exifData['ExposureTime'] = self::simplifyRational($rawExifData['ExposureTime']);
        }
        // actual F-number(F-stop) of lens when the image was taken (unsigned rational)
        if (isset($rawExifData['FNumber']) && \is_string($rawExifData['FNumber'])) {
            $exifData['FNumber'] = self::convertExifRational($rawExifData['FNumber']);
        }
        // unit is millimeter (unsigned rational)
        if (isset($rawExifData['FocalLength']) && \is_string($rawExifData['FocalLength'])) {
            $exifData['FocalLength'] = self::convertExifRational($rawExifData['FocalLength']);
        }
        if (isset($rawExifData['ISOSpeedRatings'])) {
            // CCD sensitivity equivalent to Ag-Hr film speedrate. (unsigned short)
            $exifData['ISOSpeedRatings'] = \intval($rawExifData['ISOSpeedRatings']);
        }
        if (isset($rawExifData['Flash'])) {
            // Indicates the status of flash when the image was shot. (unsigned short)
            $exifData['Flash'] = \intval($rawExifData['Flash']);
        }

        return $exifData;
    }

    /**
     * Returns the orientation of the image based on the given exif data.
     *
     * @param mixed[] $exifData
     */
    public static function getOrientation(array $exifData): int
    {
        $orientation = self::ORIENTATION_ORIGINAL;
        if (isset($exifData['IFD0']['Orientation'])) {
            $orientation = \intval($exifData['IFD0']['Orientation']);
            if ($orientation < self::ORIENTATION_ORIGINAL || $orientation > self::ORIENTATION_270_ROTATE) {
                $orientation = self::ORIENTATION_ORIGINAL;
            }
        }

        return $orientation;
    }

    #[\NoDiscard("as the sanitized version is written to a temporary file")]
    public static function getFileWithoutExifData(string $pathname): ?string
    {
        $fileExtension = match (FileUtil::getMimeType($pathname)) {
            'image/jpeg' => 'jpg',
            'image/gif' => 'gif',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'bin',
        };

        if ($fileExtension === 'webp') {
            return self::getWebpWithoutExif($pathname);
        }

        if (!\class_exists(\Imagick::class)) {
            return null;
        }

        $exifData = self::getExifData($pathname);
        $hasIdf0 = ($exifData['IFD0'] ?? []) !== [];
        $hasGPS = ($exifData['GPS'] ?? []) !== [];
        $hasEXIF = ($exifData['EXIF'] ?? []) !== [];
        if (!$hasIdf0 && !$hasGPS && !$hasEXIF) {
            return null;
        }

        try {
            $img = new \Imagick($pathname);
        } catch (\ImagickException) {
            return null;
        }

        $profiles = $img->getImageProfiles('icc', true);
        $img->stripImage();
        if ($profiles !== []) {
            $img->profileImage('icc', $profiles['icc']);
        }

        $tmpFile = FileUtil::getTemporaryFilename('fileWithoutExif_', ".{$fileExtension}");
        $img->writeImage($tmpFile);

        return $tmpFile;
    }

    private static function getWebpWithoutExif(string $pathname): ?string
    {
        $decoder = new Decoder();

        try {
            $webp = $decoder->fromBinary(\file_get_contents($pathname));
        } catch (WebpExifException) {
            return null;
        }

        $hasExif = $webp->getExif();
        $hasXmp = $webp->getXmp();
        if (!$hasExif && !$hasXmp) {
            return null;
        }

        if ($hasExif) {
            $webp = $webp->withExif(null);
        }
        if ($hasXmp) {
            $webp = $webp->withXmp(null);
        }

        $encoder = new Encoder();
        $bytes = $encoder->fromWebP($webp);

        $tmpFile = FileUtil::getTemporaryFilename('fileWithoutExif_', '.webp');
        \file_put_contents($tmpFile, $bytes);

        return $tmpFile;
    }

    /**
     * Converts the format of exif geo tagging coordinates.
     */
    private static function convertCoordinateToDecimal(string $coordinate): float
    {
        $result = 0.0;
        $coordinateData = \explode('/', $coordinate);
        for ($i = 0, $j = \count($coordinateData); $i < $j; $i++) {
            if ($i == 0) {
                $result = (float)$coordinateData[0];
            } elseif ($coordinateData[$i]) {
                $result /= (float)$coordinateData[$i];
            }
        }

        return $result;
    }

    /**
     * Converts a exif rational value to a float.
     */
    private static function convertExifRational(string $rational): float
    {
        $data = \explode('/', $rational);
        if (\count($data) == 1) {
            return \floatval($rational);
        }

        // prevent division by zero if 2nd value is invalid
        $data[1] = \floatval($data[1]);
        if (!$data[1]) {
            return 0.0;
        }

        return \floatval($data[0]) / $data[1];
    }

    private static function simplifyRational(string $rational): string
    {
        $data = \explode('/', $rational);
        if (\count($data) == 1) {
            return $rational;
        }

        $numerator = (int)$data[0];
        $denonimator = (int)$data[1];
        $gcd = self::gcd($numerator, $denonimator);

        // When the numerator equals 0 (e.g. 0/10), then the resulting GCD will
        // equal null, resulting in a division by zero below.
        if ($gcd === 0) {
            return $rational;
        }

        return \sprintf('%d/%d', \intdiv($numerator, $gcd), \intdiv($denonimator, $gcd));
    }

    /**
     * Implements the Euclidian Algorithm to calculate the GCD.
     */
    private static function gcd(int $a, int $b): int
    {
        while ($b != 0) {
            $t = $b;
            $b = $a % $b;
            $a = $t;
        }

        return $a;
    }
}
