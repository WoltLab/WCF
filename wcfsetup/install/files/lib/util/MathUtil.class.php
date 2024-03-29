<?php

namespace wcf\util;

/**
 * Contains math-related functions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class MathUtil
{
    /**
     * @deprecated 5.5 - Use `\random_int()` or `\mt_rand()` directly.
     */
    public static function getRandomValue($min = null, $max = null)
    {
        // generate random value
        return ($min !== null && $max !== null) ? \mt_rand($min, $max) : \mt_rand();
    }

    /**
     * Transforms the given latitude and longitude into cartesian coordinates
     * (x, y, z).
     *
     * @param float $latitude
     * @param float $longitude
     * @return  float[]
     */
    public static function latitudeLongitudeToCartesian($latitude, $longitude)
    {
        $lambda = $longitude * \M_PI / 180;
        $phi = $latitude * \M_PI / 180;

        return [
            6371 * \cos($phi) * \cos($lambda),    // x
            6371 * \cos($phi) * \sin($lambda),    // y
            6371 * \sin($phi),            // z
        ];
    }

    /**
     * Forbid creation of MathUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
