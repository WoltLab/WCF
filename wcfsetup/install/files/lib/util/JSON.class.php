<?php
namespace wcf\util;

/**
 * Provides methods for JSON.
 *
 * @author 	Alexander Ebert
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category 	Community Framework
 */
class JSON {
	/**
	 * Returns the JSON representation of a value.
	 * 
	 * @param	mixed		$data
	 * @return	string
	 */
	public static function encode($data) {
		return json_encode($data);
	}
	
	/**
	 * Decodes a JSON string.
	 * 
	 * @param	string		$json
	 * @param	boolean		$asArray
	 * @return	array
	 */
	public static function decode($json, $asArray = true) {
		// decodes JSON
		$data = json_decode($json, $asArray);
		
		return $data;
	}
	
	/**
	 * Returns the last error occurred.
	 * 
	 * @return	integer
	 */
	public static function getLastError() {
		return json_last_error();
	}
}