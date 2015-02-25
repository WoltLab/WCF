<?php
namespace wcf\system\option\user\group;
use wcf\data\bbcode\BBCodeCache;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\option\AbstractOptionType;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * User group option type implementation for BBCode select lists.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class BBCodeSelectUserGroupOptionType extends AbstractOptionType implements IUserGroupOptionType {
	/**
	 * available BBCodes
	 * @var	array<string>
	 */
	protected $bbCodes = null;
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) {
			$newValue = array();
		}
		
		return implode(',', $newValue);
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		if ($this->bbCodes === null) {
			$this->loadBBCodeSelection();
		}
		
		$selectedBBCodes = array();
		if ($value == 'all') {
			$selectedBBCodes = $this->bbCodes;
		}
		else {
			$selectedBBCodes = explode(',', $value);
		}
		
		WCF::getTPL()->assign(array(
			'bbCodes' => $this->bbCodes,
			'option' => $option,
			'selectedBBCodes' => $selectedBBCodes
		));
		
		return WCF::getTPL()->fetch('bbCodeSelectOptionType');
	}
	
	/**
	 * Loads the list of BBCodes for the HTML select element.
	 * 
	 * @return	array<string>
	 */
	protected function loadBBCodeSelection() {
		$this->bbCodes = array_keys(BBCodeCache::getInstance()->getBBCodes());
		asort($this->bbCodes);
	}
	
	/**
	 * @see	\wcf\system\option\user\group\IUserGroupOptionType::merge()
	 */
	public function merge($defaultValue, $groupValue) {
		if ($this->bbCodes === null) {
			$this->loadBBCodeSelection();
		}
		
		if ($defaultValue == 'all') {
			$defaultValue = $this->bbCodes;
		}
		else if (empty($defaultValue) || $defaultValue == 'none') {
			$defaultValue = array();
		}
		else {
			$defaultValue = explode(',', StringUtil::unifyNewlines($defaultValue));
		}
		if ($groupValue == 'all') {
			$groupValue = $this->bbCodes;
		}
		else if (empty($groupValue) || $groupValue == 'none') {
			$groupValue = array();
		}
		else {
			$groupValue = explode(',', StringUtil::unifyNewlines($groupValue));
		}
		
		$newValue = array_unique(array_merge($defaultValue, $groupValue));
		sort($newValue);
		
		return implode(',', $newValue);
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!is_array($newValue)) {
			$newValue = array();
		}
		
		if ($this->bbCodes === null) {
			$this->loadBBCodeSelection();
		}
		
		foreach ($newValue as $tag) {
			if (!in_array($tag, $this->bbCodes)) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::compare()
	 */
	public function compare($value1, $value2) {
		$value1 = explode(',', $value1);
		$value2 = explode(',', $value2);
		
		// handle special 'all' value
		if (in_array('all', $value1)) {
			if (in_array('all', $value2)) {
				return 0;
			}
			else {
				return 1;
			}
		}
		else if (in_array('all', $value2)) {
			return -1;
		}
		
		// check if value1 contains more BBCodes than value2
		$diff = array_diff($value1, $value2);
		if (!empty($diff)) {
			return 1;
		}
		
		// check if value1 contains less BBCodes than value2
		$diff = array_diff($value2, $value1);
		if (!empty($diff)) {
			return -1;
		}
		
		// both lists of BBCodes are equal
		return 0;
	}
}
