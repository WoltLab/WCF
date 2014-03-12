<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\system\language\LanguageFactory;

/**
 * This class provides default implementations for a list of dynamic user options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
abstract class UserOptionListForm extends AbstractOptionListForm {
	/**
	 * @see	\wcf\acp\form\AbstractOptionListForm::$optionHandlerClassName
	 */
	public $optionHandlerClassName = 'wcf\system\option\user\UserOptionHandler';
	
	/**
	 * @see	\wcf\acp\form\AbstractOptionListForm::$supportI18n
	 */
	public $supportI18n = false;
	
	/**
	 * Returns a list of all available user groups.
	 * 
	 * @return	array
	 */
	protected function getAvailableGroups() {
		return UserGroup::getAccessibleGroups(array(), array(UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS));
	}
	
	/**
	 * Returns the default form language id.
	 * 
	 * @return	integer		$languageID
	 */
	protected function getDefaultFormLanguageID() {
		return LanguageFactory::getInstance()->getDefaultLanguageID();
	}
}
