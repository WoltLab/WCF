<?php
namespace wcf\util;
use wcf\system\exception\SystemException;

/**
 * Provides methods for JSON.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class JSON {
	/**
	 * All < and > are converted to \u003C and \u003E.
	 * @var	integer
	 */
	const HEX_TAG = JSON_HEX_TAG;

	/**
	 * All &s are converted to \u0026.
	 * @var	integer
	 */
	const HEX_AMP = JSON_HEX_AMP;

	/**
	 * All ' are converted to \u0027.
	 * @var	integer
	 */
	const HEX_APOS = JSON_HEX_APOS;

	/**
	 * All " are converted to \u0022.
	 * @var	integer
	 */
	const HEX_QUOT = JSON_HEX_QUOT;

	/**
	 * Outputs an object rather than an array when a non-associative array is used.
	 * @var	integer
	 */
	const FORCE_OBJECT = JSON_FORCE_OBJECT;

	/**
	 * Encodes numeric strings as numbers.
	 * @var	integer
	 */
	const NUMERIC_CHECK = JSON_NUMERIC_CHECK;

	/**
	 * Encodes large integers as their original string value.
	 * @var	integer
	 */
	const BIGINT_AS_STRING = JSON_BIGINT_AS_STRING;

	/**
	 * Use whitespace in returned data to format it.
	 * @var	integer
	 */
	const PRETTY_PRINT = JSON_PRETTY_PRINT;

	/**
	 * Don't escape /.
	 * @var	integer
	 */
	const UNESCAPED_SLASHES = JSON_UNESCAPED_SLASHES;

	/**
	 * Encode multibyte Unicode characters literally (default is to escape as \uXXXX).
	 * @var	integer
	 */
	const UNESCAPED_UNICODE = JSON_UNESCAPED_UNICODE;

	/**
	 * Substitute some unencodable values instead of failing.
	 * @var	integer
	 */
	const PARTIAL_OUTPUT_ON_ERROR = JSON_PARTIAL_OUTPUT_ON_ERROR;
	
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
