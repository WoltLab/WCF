<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\system\language\LanguageFactory;
use wcf\system\option\user\UserOptionHandler;

/**
 * This class provides default implementations for a list of dynamic user options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
abstract class UserOptionListForm extends AbstractOptionListForm {
	/**
	 * @inheritDoc
	 */
	public $optionHandlerClassName = UserOptionHandler::class;
	
	/**
	 * @inheritDoc
	 */
	public $supportI18n = false;
	
	/**
	 * Returns a list of all available user groups.
	 * 
	 * @return	UserGroup[]
	 */
	protected function getAvailableGroups() {
		return UserGroup::getSortedAccessibleGroups([], [UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]);
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
