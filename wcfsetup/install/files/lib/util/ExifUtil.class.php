<?php

namespace wcf\util;

/**
 * Provides exif-related functions.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Util
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
     * @param string $filename
     * @return  array
     */
    public static function getExifData($filename)
    {
        if (\function_exists('exif_read_data')) {
            $exifData = @\exif_read_data($filename, '', true);
            if ($exifData !== false) {
                return $exifData;
            }
        }

        return [];
    }

    /**
     * Returns the name of the used camera based on the given exif data.
     *
     * @param array $exifData
     * @return  string
     */
    public static function getCamera(array $exifData)
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
     * @param array $exifData
     * @return  string
     */
    public static function getCreationTime(array $exifData)
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
     * @param array $exifData
     * @return  float
     */
    public static function getLongitude(array $exifData)
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
     * @param array $exifData
     * @return  float
     */
    public static function getLatitude(array $exifData)
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
     * @param array $rawExifData
     * @return  array
     */
    public static function getFormattedExifData(array $rawExifData)
    {
        $exifData = [];

        // unit is second (unsigned rational)
        if (isset($rawExifData['ExposureTime']) && \is_string($rawExifData['ExposureTime'])) {
            $exifData['ExposureTime'] = $rawExifData['ExposureTime'];
        }
        // actual F-number(F-stop) of lens when the image was taken (unsigned rational)
        if (isset($rawExifData['FNumber']) && \is_string($rawExifData['FNumber'])) {
            $exifData['FNumber'] = self::convertExifRational($rawExifData['FNumber']);
        }
        // unit is millimeter (unsigned rational)
        if (isset($rawExifData['FocalLength']) && \is_string($rawExifData['FocalLength'])) {
            $exifData['FocalLength'] = self::convertExifRational($rawExifData['FocalLength']);
        }
        /*if (isset($rawExifData['ShutterSpeedValue']) && is_string($rawExifData['ShutterSpeedValue'])) {
            // To convert this value to ordinary 'Shutter Speed'; calculate this value's power of 2, then reciprocal.
            // For example, if value is '4', shutter speed is 1/(2^4)=1/16 second. (signed rational)
            $exifData['ShutterSpeedValue'] = '1/' . round(pow(2, self::convertExifRational($rawExifData['ShutterSpeedValue'])), 0);
        }*/
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
     * @param array $exifData
     * @return  int
     */
    public static function getOrientation(array $exifData)
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

    /**
     * Converts the format of exif geo tagging coordinates.
     *
     * @param string $coordinate
     * @return  double
     */
    private static function convertCoordinateToDecimal($coordinate)
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
     *
     * @param string $rational
     * @return  float
     */
    private static function convertExifRational($rational)
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
}
