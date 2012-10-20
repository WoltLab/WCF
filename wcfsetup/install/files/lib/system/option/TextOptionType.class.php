<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * TextOptionType is an implementation of IOptionType for 'input type="text"' tags.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class TextOptionType extends AbstractOptionType implements ISearchableUserOption {
	/**
	 * input type
	 * @var	string
	 */
	protected $inputType = 'text';
	
	/**
	 * input css class
	 * @var	string
	 */
	protected $inputClass = 'long';
	
	/**
	 * @see	wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'inputType' => $this->inputType,
			'inputClass' => $this->inputClass,
			'value' => $value
		));
		return WCF::getTPL()->fetch('textOptionType');
	}
	
	/**
	 * @see	wcf\system\option\ISearchableUserOption::getSearchFormElement()
	 */
	public function getSearchFormElement(Option $option, $value) {
		return $this->getFormElement($option, $value);
	}
	
	/**
	 * @see	wcf\system\option\ISearchableUserOption::getCondition()
	 */
	public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, $value) {
		$value = StringUtil::trim($value);
		if (empty($value)) return false;
		
		$conditions->add("option_value.userOption".$option->optionID." LIKE ?", array('%'.addcslashes($value, '_%').'%'));
		return true;
	}
}
