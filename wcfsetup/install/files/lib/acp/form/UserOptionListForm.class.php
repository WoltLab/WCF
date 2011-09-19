<?php
namespace wcf\acp\form;
use wcf\data\option\Option;
use wcf\data\user\group\UserGroup;
use wcf\system\WCF;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\util\StringUtil;

/**
 * This class provides default implementations for a list of dynamic user options.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
abstract class UserOptionListForm extends AbstractOptionListForm {
	/**
	 * @see wcf\acp\form\AbstractOptionListForm::$cacheName
	 */
	public $cacheName = 'user-option-';
	
	/**
	 * Returns a list of all available user groups.
	 * 
	 * @return	array
	 */
	protected function getAvailableGroups() {
		return UserGroup::getAccessibleGroups(array(), array(UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS));
	}
	
	/**
	 * Returns a list of all available languages.
	 * 
	 * @return	array
	 */
	protected function getAvailableLanguages() {
		$availableLanguages = array();
		foreach (LanguageFactory::getInstance()->getAvailableLanguages(PACKAGE_ID) as $language) {
			$availableLanguages[$language['languageID']] = WCF::getLanguage()->get('wcf.global.language.'.$language['languageCode']);	
		}
		
		// sort languages
		StringUtil::sort($availableLanguages);
		
		return $availableLanguages;
	}
	
	/**
	 * Returns a list of all available content languages.
	 * 
	 * @return	array
	 */
	public static function getAvailableContentLanguages() {
		$availableLanguages = array();
		foreach (LanguageFactory::getInstance()->getAvailableContentLanguages(PACKAGE_ID) as $language) {
			$availableLanguages[$language['languageID']] = WCF::getLanguage()->get('wcf.global.language.'.$language['languageCode']);	
		}
		
		// sort languages
		StringUtil::sort($availableLanguages);
		
		return $availableLanguages;
	}
	
	/**
	 * Returns the default-form language id    
	 * 
	 * @return 	integer		$languageID
	 */
	protected function getDefaultFormLanguageID() {
		return LanguageFactory::getInstance()->getDefaultLanguageID();
	}
	
	/**
	 * @see wcf\acp\form\AbstractOptionListForm::validateOption()
	 */
	protected function validateOption(Option $option) {
		parent::validateOption($option);

		if ($option->required && empty($this->optionValues[$option->optionName])) {
			throw new UserInputException($option->optionName);
		}
	}
}
