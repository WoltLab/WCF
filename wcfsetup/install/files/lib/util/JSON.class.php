<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
 * Provides methods for JSON.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class JSON {
	/**
	 * Returns the JSON representation of a value.
	 * 
	 * @param	mixed		$data
	 * @param	integer		$options
	 * @return	string
	 */
	public static function encode($data, $options = 0) {
		return json_encode($data, $options);
	}
	
	/**
	 * Decodes a JSON string.
	 * 
	 * @param	string		$json
	 * @param	boolean		$asArray
	 * @return	array
	 * @throws	SystemException
	 */
	public static function decode($json, $asArray = true) {
		// decodes JSON
		$data = @json_decode($json, $asArray);
		
		if ($data === null && self::getLastError() !== JSON_ERROR_NONE) {
			throw new SystemException('Could not decode JSON (error '.self::getLastError().'): '.$json);
		}
		
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
	
	/**
	 * Forbid creation of JSON objects.
	 */
	private function __construct() {
		// does nothing
	}
}
