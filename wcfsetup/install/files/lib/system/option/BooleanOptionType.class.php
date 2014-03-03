<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Option type implementation for boolean values.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class BooleanOptionType extends AbstractOptionType implements ISearchableUserOption {
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		$options = Option::parseEnableOptions($option->enableOptions);
		
		WCF::getTPL()->assign(array(
			'disableOptions' => $options['disableOptions'],
			'enableOptions' => $options['enableOptions'],
			'option' => $option,
			'value' => $value
		));
		return WCF::getTPL()->fetch('booleanOptionType');
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if ($newValue !== null) return 1;
		return 0;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getCSSClassName()
	 */
	public function getCSSClassName() {
		return 'reversed';
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(Option $option, $value) {
		return $this->getFormElement($option, $value);
	}
	
	/**
	 * @see	\wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		$value = intval($value);
		if (!$value) return false;
		
		$conditions->add("option_value.userOption".$option->optionID." = ?", array(1));
		return true;
	}
}
