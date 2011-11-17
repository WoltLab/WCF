<?php
namespace wcf\system\option\user;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\data\user\User;
use wcf\system\exception\UserInputException;
use wcf\system\option\OptionHandler;

/**
 * Default implementation for user option lists.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option
 * @category 	Community Framework
 */
class UserOptionHandler extends OptionHandler {
	public function setUser(User $user) {
		$this->optionValues = array();
		
		foreach ($this->options as $option) {
			$userOption = 'userOption' . $option->optionID;
			$this->optionValues[$option->optionName] = $user->{$userOption};
		}
	}
	
	/**
	 * @see	wcf\system\option\OptionHandler::validateOption()
	 */
	protected function validateOption(Option $option) {
		parent::validateOption($option);
		
		if ($option->required && empty($this->optionValues[$option->optionName])) {
			throw new UserInputException($option->optionName);
		}
	}
	
	/**
	 * @see	wcf\system\option\OptionHandler::checkCategory()
	 */
	protected function checkCategory(OptionCategory $category) {
		if ($category->categoryName == 'hidden') {
			return false;
		}
		
		return parent::checkCategory($category);
	}
}
