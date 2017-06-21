<?php
namespace wcf\system\option;
use wcf\data\custom\option\CustomOption;
use wcf\data\option\Option;
use wcf\system\exception\NotImplementedException;
use wcf\system\exception\UserInputException;

abstract class CustomOptionHandler extends OptionHandler {
	/**
	 * Gets all options and option categories from cache.
	 */
	protected function readCache() {
		throw new NotImplementedException();
		
		$this->cachedOptions = FileOptionCacheBuilder::getInstance()->getData();
	}
	
	/**
	 * Initializes active options.
	 */
	public function init() {
		if (!$this->didInit) {
			// get active options
			foreach ($this->cachedOptions as $option) {
				if ($this->checkOption($option)) {
					$this->options[$option->optionName] = $option;
				}
			}
			
			// mark options as initialized
			$this->didInit = true;
		}
	}
	
	/**
	 * Returns the parsed options.
	 *
	 * @return	array
	 */
	public function getOptions() {
		$parsedOptions = [];
		foreach ($this->options as $option) {
			$parsedOptions[] = $this->getOption($option->optionName);
		}
		
		return $parsedOptions;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		/** @var CustomOption $option */
		foreach ($this->options as $option) {
			if (!isset($this->optionValues[$option->optionName])) {
				$this->optionValues[$option->optionName] = $option->defaultValue;
			}
		}
	}
	
	/**
	 * Resets the option values.
	 */
	public function resetOptionValues() {
		$this->optionValues = [];
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
		
		if (isset($this->optionValues[$optionName])) {
			/** @noinspection PhpUndefinedMethodInspection */
			$optionData['object']->setOptionValue($this->optionValues[$optionName]);
		}
		
		return $optionData;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateOption(Option $option) {
		/** @var CustomOption $option */
		
		parent::validateOption($option);
		
		if ($option->required && $option->optionType != 'boolean' && empty($this->optionValues[$option->optionName])) {
			throw new UserInputException($option->optionName);
		}
	}
}
