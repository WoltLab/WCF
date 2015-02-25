<?php
namespace wcf\data\user\option;
use wcf\data\user\User;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\util\ClassUtil;
use wcf\util\StringUtil;

/**
 * Represents a viewable user option.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option
 * @category	Community Framework
 */
class ViewableUserOption extends DatabaseObjectDecorator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\option\UserOption';
	
	/**
	 * list of output objects
	 * @var	array<\wcf\system\option\user\IUserOptionOutput>
	 */
	public static $outputObjects = array();
	
	/**
	 * cached user options
	 * @var	array<\wcf\data\user\option\ViewableUserOption>
	 */
	public static $userOptions = array();
	
	/**
	 * user option value
	 * @var	string
	 */
	public $optionValue = '';
	
	/**
	 * Sets option values for a specific user.
	 * 
	 * @param	\wcf\data\user\User	$user
	 */
	public function setOptionValue(User $user) {
		$userOption = 'userOption' . $this->optionID;
		$optionValue = $user->{$userOption};
		
		// use output class
		if ($this->outputClass) {
			$outputObj = $this->getOutputObject();
			$this->optionValue = $outputObj->getOutput($user, $this->getDecoratedObject(), $optionValue);
		}
		else {
			$this->optionValue = StringUtil::encodeHTML($optionValue);
		}
	}
	
	/**
	 * Returns the output object for current user option.
	 * 
	 * @return	\wcf\system\option\user\IUserOptionOutput
	 */
	public function getOutputObject() {
		if (!isset(self::$outputObjects[$this->outputClass])) {
			// create instance
			if (!class_exists($this->outputClass)) {
				throw new SystemException("unable to find class '".$this->outputClass."'");
			}
			
			// validate interface
			if (!ClassUtil::isInstanceOf($this->outputClass, 'wcf\system\option\user\IUserOptionOutput')) {
				throw new SystemException("'".$this->outputClass."' does not implement 'wcf\system\option\user\IUserOptionOutput'");
			}
			
			self::$outputObjects[$this->outputClass] = new $this->outputClass();
		}
		
		return self::$outputObjects[$this->outputClass];
	}
	
	/**
	 * Returns the user option with the given name
	 * 
	 * @param	string		$name
	 * @return	\wcf\data\user\option\ViewableUserOption
	 */
	public static function getUserOption($name) {
		if (!isset(self::$userOptions[$name])) {
			$options = UserOptionCacheBuilder::getInstance()->getData(array(), 'options');
			self::$userOptions[$name] = new ViewableUserOption($options[$name]);
		}
		
		return self::$userOptions[$name];
	}
}
