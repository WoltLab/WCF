<?php
namespace wcf\data\user\option;
use wcf\data\user\User;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\exception\SystemException;
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
	 * @param	string			$outputType
	 */
	public function setOptionValue(User $user, $outputType = 'normal') {
		$userOption = 'userOption' . $this->optionID;
		$optionValue = $user->{$userOption};
		
		// use output class
		if ($this->outputClass) {
			$outputObj = $this->getOutputObject();
			
			if ($outputObj instanceof IUserOptionOutputContactInformation) {
				$this->outputData = $outputObj->getOutputData($user, $this->getDecoratedObject(), $optionValue);
			}
			
			if ($outputType == 'normal') $this->optionValue = $outputObj->getOutput($user, $this->getDecoratedObject(), $optionValue);
			else if ($outputType == 'short') $this->optionValue = $outputObj->getShortOutput($user, $this->getDecoratedObject(), $optionValue);
			else $outputType = $outputObj->getMediumOutput($user, $this->getDecoratedObject(), $optionValue);
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
		if (!isset(self::$outputObjects[$this->outputClass])) {
			// create instance
			if (!class_exists($this->outputClass)) {
				throw new SystemException("unable to find class '".$this->outputClass."'");
			}
			
			// validate interface
			if (!ClassUtil::isInstanceOf($this->outputClass, 'wcf\system\option\user\IUserOptionOutput')) {
				throw new SystemException("'".$this->outputClass."' should implement wcf\system\option\user\IUserOptionOutput");
			}
			
			self::$outputObjects[$this->outputClass] = new $this->outputClass();
		}
		
		return self::$outputObjects[$this->outputClass];
	}
}
