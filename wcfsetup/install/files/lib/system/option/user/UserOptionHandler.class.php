<?php
namespace wcf\system\option\user;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\data\user\option\ViewableUserOption;
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
	/**
	 * current user
	 * @var	wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * Sets option values for a certain user.
	 * 
	 * @param	wcf\data\user\User
	 */
	public function setUser(User $user) {
		$this->optionValues = array();
		$this->user = $user;
		
		foreach ($this->options as $option) {
			$userOption = 'userOption' . $option->optionID;
			$this->optionValues[$option->optionName] = $this->user->{$userOption};
		}
		
		if (!$this->didInit) {
			$this->loadActiveOptions($this->categoryName);
			
			$this->didInit = true;
		}
	}
	
	/**
	 * @see	wcf\system\option\OptionHandler::getCategoryOptions()
	 */
	public function getCategoryOptions($categoryName = '', $inherit = true) {
		$options = parent::getCategoryOptions($categoryName, $inherit);
		
		foreach ($options as $optionData) {
			$optionData['object'] = new ViewableUserOption($optionData['object']);
			$optionData['object']->setOptionValue($this->user);
		}
		die('<pre>'.print_r($options, true));
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
	
	/**
	 * @see	wcf\system\option\OptionHandler::checkVisibility()
	 */
	protected function checkVisibility(Option $option) {
		$option->setUser($this->user);
		
		return $option->isVisible();
	}
}
