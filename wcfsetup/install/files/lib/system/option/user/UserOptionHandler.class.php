<?php
namespace wcf\system\option\user;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\ViewableUserOption;
use wcf\data\user\User;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\option\ISearchableConditionUserOption;
use wcf\system\option\OptionHandler;
use wcf\util\DateUtil;
use wcf\util\MessageUtil;

/**
 * Handles user options.
 * 
 * @author	Alexander Ebert, Joshua Ruesweg
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Option\User
 */
class UserOptionHandler extends OptionHandler {
	/**
	 * @inheritDoc
	 */
	protected $cacheClass = UserOptionCacheBuilder::class;
	
	/**
	 * true if within registration process
	 * @var	boolean
	 */
	public $inRegistration = false;
	
	/**
	 * true if within edit mode
	 * @var	boolean
	 */
	public $editMode = true;
	
	/**
	 * true if within search mode
	 * @var	boolean
	 */
	public $searchMode = false;
	
	/**
	 * true if empty options should be removed
	 * @var	boolean
	 */
	public $removeEmptyOptions = false;
	
	/**
	 * current user
	 * @var	\wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * true if the condition mode during search mode is enabled
	 * @var	boolean
	 */
	public $conditionMode = false;
	
	/**
	 * Shows empty options.
	 * 
	 * @param	boolean		$show
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
		$this->optionValues = [];
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
		$this->optionValues = [];
	}
	
	/**
	 * Enables the condition mode.
	 * 
	 * If condition mode is enabled, only options whose type implements ISearchableConditionUserOption
	 * are considered. Furthermore, the visibility setting of the option is disregarded to ensure that
	 * during automatic cronjob execution (always done as a guest), the conditions are properly set. 
	 * 
	 * @param	boolean		$enable
	 */
	public function enableConditionMode($enable = true) {
		if (!$this->searchMode) {
			$this->enableSearchMode();
		}
		
		$this->conditionMode = $enable;
	}
	
	/**
	 * Returns the option values.
	 * 
	 * @return	array
	 */
	public function getOptionValues() {
		return $this->optionValues;
	}
	
	/**
	 * Sets the option values.
	 * 
	 * @param	array		$values
	 */
	public function setOptionValues(array $values) {
		$this->optionValues = $values;
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	protected function getFormElement($type, Option $option) {
		if ($this->searchMode) return $this->getTypeObject($type)->getSearchFormElement($option, (isset($this->optionValues[$option->optionName]) ? $this->optionValues[$option->optionName] : null));
		
		return parent::getFormElement($type, $option);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateOption(Option $option) {
		parent::validateOption($option);
		
		if ($option->required && $option->optionType != 'boolean' && empty($this->optionValues[$option->optionName])) {
			throw new UserInputException($option->optionName);
		}
		
		if (REGISTER_MIN_USER_AGE) {
			if ($this->inRegistration && $option->optionName == 'birthday') {
				if (empty($this->optionValues[$option->optionName])) {
					throw new UserInputException($option->optionName);
				}
				
				if (DateUtil::getAge($this->optionValues[$option->optionName]) < REGISTER_MIN_USER_AGE) {
					throw new UserInputException($option->optionName, 'birthdayTooYoung');
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function checkCategory(OptionCategory $category) {
		if ($category->categoryName == 'hidden') {
			return false;
		}
		
		return parent::checkCategory($category);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function checkVisibility(Option $option) {
		/** @var UserOption $option */
		
		if ($option->isDisabled) {
			return false;
		}
		
		// in registration
		if ($this->inRegistration && !$option->askDuringRegistration && !$option->required && !($option->editable & UserOption::EDITABILITY_OWNER_DURING_REGISTRATION) && ($option->optionName != 'birthday' || !REGISTER_MIN_USER_AGE)) {
			return false;
		}
		
		// search mode
		if ($this->searchMode && !$option->searchable) {
			return false;
		}
		
		if ($this->conditionMode && (!$this->getTypeObject($option->optionType) instanceof ISearchableConditionUserOption)) {
			return false;
		}
		
		if ($this->user !== null) {
			$option->setUser($this->user);
		}
		
		if ($this->editMode) {
			return $option->isEditable($this->inRegistration);
		}
		else if (!$this->conditionMode) {
			return $option->isVisible();
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function save($categoryName = null, $optionPrefix = null) {
		$options = parent::save($categoryName, $optionPrefix);
		
		// remove options which are not asked during registration
		if ($this->inRegistration && !empty($options)) {
			foreach ($this->options as $option) {
				if (array_key_exists($option->optionID, $options) && !$option->askDuringRegistration && !($option->editable & UserOption::EDITABILITY_OWNER_DURING_REGISTRATION) && !$option->required && ($option->optionName != 'birthday' || !REGISTER_MIN_USER_AGE)) {
					unset($options[$option->optionID]);
				}
			}
		}
		
		return $options;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		foreach ($this->options as $option) {
			if (!isset($this->optionValues[$option->optionName])) $this->optionValues[$option->optionName] = $option->defaultValue;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readUserInput(array &$source) {
		parent::readUserInput($source);
		
		// remove 4 byte utf-8 characters (e.g. emoji)
		foreach ($this->rawValues as &$value) {
			if (is_string($value)) $value = MessageUtil::stripCrap($value);
		}
		
		if ($this->searchMode) {
			$this->optionValues = $this->rawValues;
		}
	}
}
