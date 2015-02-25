<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Option type implementation for textareas.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class TextareaOptionType extends TextOptionType {
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'value' => $value
		));
		return WCF::getTPL()->fetch('textareaOptionType');
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'searchOption' => $value !== null && ($value !== $option->defaultValue || isset($_POST['searchOptions'][$option->optionName])),
			'value' => $value
		));
		return WCF::getTPL()->fetch('textareaSearchableOptionType');
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		$newValue = StringUtil::unifyNewlines(parent::getData($option, $newValue));
		
		// check for wildcard
		if ($option->wildcard) {
			$values = explode("\n", $newValue);
			if (in_array($option->wildcard, $values)) {
				$newValue = $option->wildcard;
			}
		}
		
		return $newValue;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::compare()
	 */
	public function compare($value1, $value2) {
		$value1 = explode("\n", StringUtil::unifyNewlines($value1));
		$value2 = explode("\n", StringUtil::unifyNewlines($value2));
		
		// check if value1 contains more elements than value2
		$diff = array_diff($value1, $value2);
		if (!empty($diff)) {
			return 1;
		}
		
		// check if value1 contains less elements than value2
		$diff = array_diff($value2, $value1);
		if (!empty($diff)) {
			return -1;
		}
		
		// both lists are equal
		return 0;
	}
}
