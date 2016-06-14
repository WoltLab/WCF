<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Option type implementation for textareas.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option
 */
class TextareaOptionType extends TextOptionType {
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign([
			'option' => $option,
			'value' => $value
		]);
		return WCF::getTPL()->fetch('textareaOptionType');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getSearchFormElement(Option $option, $value) {
		WCF::getTPL()->assign([
			'option' => $option,
			'searchOption' => $value !== null && ($value !== $option->defaultValue || isset($_POST['searchOptions'][$option->optionName])),
			'value' => $value
		]);
		return WCF::getTPL()->fetch('textareaSearchableOptionType');
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
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
