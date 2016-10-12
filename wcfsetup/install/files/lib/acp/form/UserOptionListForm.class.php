<?php
namespace wcf\acp\form;
use wcf\data\user\group\UserGroup;
use wcf\system\language\LanguageFactory;
use wcf\system\option\user\UserOptionHandler;

/**
 * This class provides default implementations for a list of dynamic user options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
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
	 * @return	array
	 */
	protected function getAvailableGroups() {
		$userGroups = UserGroup::getAccessibleGroups([], [UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]);
		
		// work-around for PHP 5.3.3 randomly failing in uasort()
		foreach ($userGroups as $userGroup) {
			$userGroup->getName();
		}
		
		uasort($userGroups, function(UserGroup $groupA, UserGroup $groupB) {
			return strcmp($groupA->getName(), $groupB->getName());
		});
		
		return $userGroups;
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
