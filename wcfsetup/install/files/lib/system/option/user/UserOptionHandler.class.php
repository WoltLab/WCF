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
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.option.user
 * @category	Community Framework
 */
class UserOptionHandler extends OptionHandler {
	/**
	 * @see	\wcf\system\option\OptionHandler::$cacheClass
	 */
	protected $cacheClass = 'wcf\system\cache\builder\UserOptionCacheBuilder';
	
	/**
	 * true, if within registration process
	 * @var	boolean
	 */
	public $inRegistration = false;
	
	/**
	 * true, if within edit mode
	 * @var	boolean
	 */
	public $editMode = true;
	
	/**
	 * true, if within search mode
	 * @var	boolean
	 */
	public $searchMode = false;
	
	/**
	 * true, if empty options should be removed
	 * @var	boolean
	 */
	public $removeEmptyOptions = false;
	
	/**
	 * current user
	 * @var	\wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * Shows empty options.
	 */
	public function showEmptyOptions($show = true) {
		$this->removeEmptyOptions = !$show;
	}
	
	/**
	 * Sets registration mode.
	 * 
	 * @param	boolean		$inRegistration
	 */
	public function setInRegistration($inRegistration = true) {
		$this->inRegistration = $inRegistration;
		if ($inRegistration) $this->enableEditMode();
	}
	
	/**
	 * Enables edit mode.
	 * 
	 * @param	boolean		$enable
	 */
	public function enableEditMode($enable = true) {
		$this->editMode = $enable;
	}
	
	/**
	 * Enables search mode.
	 * 
	 * @param	boolean		$enable
	 */
	public function enableSearchMode($enable = true) {
		$this->searchMode = $enable;
		if ($enable) $this->enableEditMode(false);
	}
	
	/**
	 * Sets option values for a certain user.
	 * 
	 * @param	\wcf\data\user\User	$user
	 */
	public function setUser(User $user) {
		$this->optionValues = array();
		$this->user = $user;
		
		$this->init();
		foreach ($this->options as $option) {
			$userOption = 'userOption' . $option->optionID;
			$this->optionValues[$option->optionName] = $this->user->{$userOption};
		}
	}
	
	/**
	 * Resets the option values.
	 */
	public function resetOptionValues() {
		$this->optionValues = array();
	}
	
	/**
	 * @see	\wcf\system\option\OptionHandler::getOption()
	 */
	public function getOption($optionName) {
		$optionData = parent::getOption($optionName);
		
		if (!$this->editMode && !$this->searchMode) {
			$optionData['object'] = new ViewableUserOption($optionData['object']);
			if ($this->user !== null) {
				$optionData['object']->setOptionValue($this->user);
			}
			
			if ($this->removeEmptyOptions && empty($optionData['object']->optionValue)) {
				return null;
			}
		}
		
		return $optionData;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionType::getFormElement()
	 */
	protected function getFormElement($type, Option $option) {
		if ($this->searchMode) return $this->getTypeObject($type)->getSearchFormElement($option, (isset($this->optionValues[$option->optionName]) ? $this->optionValues[$option->optionName] : null));
		
		return parent::getFormElement($type, $option);
	}
	
	/**
	 * @see	\wcf\system\option\OptionHandler::validateOption()
	 */
	protected function validateOption(Option $option) {
		parent::validateOption($option);
		
		if ($option->required && empty($this->optionValues[$option->optionName])) {
			throw new UserInputException($option->optionName);
		}
	}
	
	/**
	 * @see	\wcf\system\option\OptionHandler::checkCategory()
	 */
	protected function checkCategory(OptionCategory $category) {
		if ($category->categoryName == 'hidden') {
			return false;
		}
		
		return parent::checkCategory($category);
	}
	
	/**
	 * @see	\wcf\system\option\OptionHandler::checkVisibility()
	 */
	protected function checkVisibility(Option $option) {
		if ($option->isDisabled) {
			return false;
		}
		
		// in registration
		if ($this->inRegistration && !$option->askDuringRegistration && !$option->required) {
			return false;
		}
		
		// search mode
		if ($this->searchMode && !$option->searchable) {
			return false;
		}
		
		if ($this->user !== null) {
			$option->setUser($this->user);
		}
		
		if ($this->editMode) {
			return $option->isEditable();
		}
		else {
			return $option->isVisible();
		}
	}
	
	/**
	 * @see	\wcf\system\option\OptionHandler::save()
	 */
	public function save($categoryName = null, $optionPrefix = null) {
		$options = parent::save($categoryName, $optionPrefix);
		
		// remove options which are not asked during registration
		if ($this->inRegistration && !empty($options)) {
			foreach ($this->options as $option) {
				if (!$option->askDuringRegistration && array_key_exists($option->optionID, $options)) {
					unset($options[$option->optionID]);
				}
			}
		}
		
		return $options;
	}
	
	/**
	 * @see	\wcf\system\option\IOptionHandler::readData()
	 */
	public function readData() {
		foreach ($this->options as $option) {
			if (!isset($this->optionValues[$option->optionName])) $this->optionValues[$option->optionName] = $option->defaultValue;
		}
	}
	
	/**
	 * @see	\wcf\system\option\IOptionHandler::readUserInput()
	 */
	public function readUserInput(array &$source) {
		parent::readUserInput($source);
		
		if ($this->searchMode) {
			$this->optionValues = $this->rawValues;
		}
	}
}
