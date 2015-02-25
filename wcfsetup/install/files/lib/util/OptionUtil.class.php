<?php
namespace wcf\util;

/**
 * Contains option-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category	Community Framework
 */
final class OptionUtil {
	/**
	 * Returns a list of the available options.
	 * 
	 * @param	string		$selectOptions
	 * @return	array
	 */
	public static function parseSelectOptions($selectOptions) {
		$result = array();
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
		$result = array();
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
	
	private function __construct() { }
}
