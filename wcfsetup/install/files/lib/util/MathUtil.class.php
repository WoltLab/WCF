<?php
namespace wcf\util;

/**
 * Contains math-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class MathUtil {
	/**
	 * Generates a random value.
	 * 
	 * @param	integer		$min
	 * @param	integer		$max
	 * @return	integer
	 */
	public static function getRandomValue($min = null, $max = null) {
		// generate random value
		return (($min !== null && $max !== null) ? mt_rand($min, $max) : mt_rand());
	}
	
	/**
	 * Transforms the given latitude and longitude into cartesion coordinates
	 * (x, y, z).
	 * 
	 * @param	float		$latitude
	 * @param	float		$longitude
	 * @return	array<float>
	 */
	public static function latitudeLongitudeToCartesian($latitude, $longitude) {
		$lambda = $longitude * pi() / 180;
		$phi = $latitude * pi() / 180;
		
		return array(
				6371 * cos($phi) * cos($lambda),	// x
				6371 * cos($phi) * sin($lambda),	// y
				6371 * sin($phi)			// z
		);
	}
	
	private function __construct() { }
}
