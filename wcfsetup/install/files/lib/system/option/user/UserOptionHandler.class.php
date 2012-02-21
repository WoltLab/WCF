<?php
namespace wcf\system\option\user;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\data\user\option\ViewableUserOption;
use wcf\data\user\User;
use wcf\system\exception\UserInputException;
use wcf\system\option\OptionHandler;

/**
 * Handles user options.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user
 * @category 	Community Framework
 */
class UserOptionHandler extends OptionHandler {
	/**
	 * true, if empty options should be removed
	 * @var	boolean
	 */
	public $removeEmptyOptions = false;
	
	/**
	 * current user
	 * @var	wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * Hides empty options.
	 */
	public function hideEmptyOptions() {
		$this->removeEmptyOptions = true;
	}
	
	/**
	 * Shows empty options.
	 */
	public function showEmptyOptions() {
		$this->removeEmptyOptions = false;
	}
	
	/**
	 * Sets option values for a certain user.
	 * 
	 * @param	wcf\data\user\User	$user
	 * @param	array<string>		$ignoreCategories
	 */
	public function setUser(User $user, array $ignoreCategories = array()) {
		$this->optionValues = array();
		$this->user = $user;
		
		if (!$this->didInit) {
			$this->loadActiveOptions($this->categoryName, $ignoreCategories);
			
			$this->didInit = true;
		}
		
		foreach ($this->options as $option) {
			$userOption = 'userOption' . $option->optionID;
			$this->optionValues[$option->optionName] = $this->user->{$userOption};
		}
	}
	
	/**
	 * @see	wcf\system\option\OptionHandler::getOption()
	 */
	public function getOption($optionName) {
		$optionData = parent::getOption($optionName);
		
		$optionData['object'] = new ViewableUserOption($optionData['object']);
		if ($this->user !== null) {
			$optionData['object']->setOptionValue($this->user);
		}
		
		if ($this->removeEmptyOptions && empty($optionData['object']->optionValue)) {
			return null;
		}
		
		return $optionData;
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
		if ($this->user !== null) {
			$option->setUser($this->user);
		}
		
		return $option->isVisible();
	}
}
