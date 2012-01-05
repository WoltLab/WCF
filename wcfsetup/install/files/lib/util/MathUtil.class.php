<?php
namespace wcf\util;

/**
 * Contains math-related functions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category 	Community Framework
 */
class MathUtil {
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
}
