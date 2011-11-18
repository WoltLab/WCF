<?php
namespace wcf\data\user\option;
use wcf\data\user\User;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\option\user\IUserOptionOutputContactInformation;
use wcf\util\ClassUtil;
use wcf\util\StringUtil;

class ViewableUserOption extends DatabaseObjectDecorator {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\option\UserOption';
	
	/**
	 * list of output objects
	 * @var	array<wcf\system\option\user\IUserOptionOutput>
	 */
	public static $outputObjects = array();
	
	/**
	 * user option value
	 * @var	string
	 */
	public $optionValue = '';
	
	/**
	 * user option output data
	 * @var	array
	 */
	public $outputData = array();
	
	/**
	 * Sets option values for a specific user.
	 * 
	 * @param	wcf\data\user\User	$user
	 */
	public function setOptionValue(User $user) {
		$userOption = 'userOption' . $this->optionID;
		$optionValue = $user->{$userOption};
		
		// use output class
		if ($this->outputClass) {
			$outputObj = $this->getOutputObject($this->outputClass);
			
			if ($outputObj instanceof IUserOptionOutputContactInformation) {
				$this->outputData = $outputObj->getOutputData($user, $this->getDecoratedObject(), $optionValue);
			}
			
			if ($this->outputType == 'normal') $this->optionValue = $outputObj->getOutput($user, $this->getDecoratedObject(), $optionValue);
			else if ($this->outputType == 'short') $this->optionValue = $outputObj->getShortOutput($user, $this->getDecoratedObject(), $optionValue);
			else $this->optionValue = $outputObj->getMediumOutput($user, $this->getDecoratedObject(), $optionValue);
		}
		else {
			$this->optionValue = StringUtil::encodeHTML($optionValue);
		}
	}
	
	/**
	 * Returns the output object for current user option.
	 * 
	 * @return	wcf\system\option\user\IUserOptionOutput
	 */
	public function getOutputObject() {
		if (!isset(self::$outputObjects[$this->className])) {
			// create instance
			if (!class_exists($this->className)) {
				throw new SystemException("unable to find class '".$this->className."'");
			}
			
			// validate interface
			if (!ClassUtil::isInstanceOf($this->className, 'wcf\system\user\option\IUserOptionOutput')) {
				throw new SystemException("'".$this->className."' should implement wcf\system\user\option\IUserOptionOutput");
			}
			
			self::$outputObjects[$this->className] = new $this->className();
		}
		
		return self::$outputObjects[$this->className];
	}
}
