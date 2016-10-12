<?php
namespace wcf\util;

/**
 * Contains option-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class OptionUtil {
	/**
	 * Returns a list of the available options.
	 * 
	 * @param	string		$selectOptions
	 * @return	array
	 */
	public static function parseSelectOptions($selectOptions) {
		$result = [];
		$options = explode("\n", StringUtil::trim(StringUtil::unifyNewlines($selectOptions)));
		foreach ($options as $option) {
			$key = $value = $option;
			if (mb_strpos($option, ':') !== false) {
				$optionData = explode(':', $option);
				$key = array_shift($optionData);
				$value = implode(':', $optionData);
			}
			
			$result[$key] = $value;
		}
		
		return $result;
	}
	
	/**
	 * Returns a list of the enable options.
	 * 
	 * @param	string		$enableOptions
	 * @return	array
	 */
	public static function parseMultipleEnableOptions($enableOptions) {
		$result = [];
		if (!empty($enableOptions)) {
			$options = explode("\n", StringUtil::trim(StringUtil::unifyNewlines($enableOptions)));
			$key = -1;
			foreach ($options as $option) {
				if (mb_strpos($option, ':') !== false) {
					$optionData = explode(':', $option);
					$key = array_shift($optionData);
					$value = implode(':', $optionData);
				}
				else {
					$key++;
					$value = $option;
				}
				
				$result[$key] = $value;
			}
		}
		
		return $result;
	}
	
	/**
	 * Forbid creation of OptionUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
