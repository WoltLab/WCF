<?php
namespace wcf\system\option\user\group;
use wcf\data\option\Option;
use wcf\system\option\BooleanOptionType;
use wcf\system\WCF;

/**
 * User group option type implementation for boolean values.
 * 
 * The merge of option values returns true if at least one value is true.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User\Group
 */
class BooleanUserGroupOptionType extends BooleanOptionType implements IUserGroupOptionType, IUserGroupGroupOptionType {
	use TUserGroupOptionType;
	
	/**
	 * @inheritDoc
	 */
	public function getFormElement(Option $option, $value) {
		$options = Option::parseEnableOptions($option->enableOptions);
		
		WCF::getTPL()->assign([
			'disableOptions' => $options['disableOptions'],
			'enableOptions' => $options['enableOptions'],
			'group' => $this->userGroup,
			'option' => $option,
			'value' => $value
		]);
		
		return WCF::getTPL()->fetch('userGroupBooleanOptionType');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getData(Option $option, $newValue) {
		return ($newValue == -1) ? -1 : parent::getData($option, $newValue);
	}
	
	/**
	 * @inheritDoc
	 */
	public function merge($defaultValue, $groupValue) {
		// force value for 'Never'
		if ($defaultValue == -1 || $groupValue == -1) {
			return -1;
		}
		
		// don't save if values are equal or $defaultValue is better
		if ($defaultValue == $groupValue || $defaultValue && !$groupValue) {
			return null;
		}
		
		return $groupValue;
	}
}
