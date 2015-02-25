<?php
namespace wcf\system\option;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Option type implementation for user option selection.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category	Community Framework
 */
class UseroptionsOptionType extends AbstractOptionType {
	/**
	 * list of available user options
	 * @var	array<string>
	 */
	protected static $userOptions = null;
	
	/**
	 * @see	\wcf\system\option\IOptionType::validate()
	 */
	public function validate(Option $option, $newValue) {
		if (!is_array($newValue)) {
			$newValue = array();
		}
		
		foreach ($newValue as $optionName) {
			if (!in_array($optionName, self::getUserOptions())) {
				throw new UserInputException($option->optionName, 'validationFailed');
			}
		}
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getData()
	 */
	public function getData(Option $option, $newValue) {
		if (!is_array($newValue)) return '';
		return implode(',', $newValue);
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	public function getFormElement(Option $option, $value) {
		WCF::getTPL()->assign(array(
			'option' => $option,
			'value' => explode(',', $value),
			'availableOptions' => self::getUserOptions()
		));
		return WCF::getTPL()->fetch('useroptionsOptionType');
	}
	
	/**
	 * Returns the list of available user options.
	 * 
	 * @return	string
	 */
	protected static function getUserOptions() {
		if (self::$userOptions === null) {
			self::$userOptions = array();
			$sql = "SELECT	optionName
				FROM	wcf".WCF_N."_user_option
				WHERE	categoryName IN (
						SELECT	categoryName
						FROM	wcf".WCF_N."_user_option_category
						WHERE	parentCategoryName = 'profile'	
					)
					AND optionType <> 'boolean'";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute();
			while ($row = $statement->fetchArray()) {
				self::$userOptions[] = $row['optionName'];
			}
		}
		
		return self::$userOptions;
	}
}
