<?php
namespace wcf\acp\form;
use wcf\data\language\LanguageEditor;
use wcf\system\language\LanguageFactory;
use wcf\system\exception\UserInputException;
use wcf\system\cache\CacheHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\ArrayUtil;

/**
 * Shows the language multilingualism form.
 * 
 * @author	Jean-Marc Licht
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.acp.language
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class LanguageMultilingualismForm extends ACPForm {
	/**
	 * @see wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.multilingualism';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.language.canEditLanguage');
	
	// data
	public $enable = 0;
	public $languageIDs = array();
	public $languages = array();
	public $languageList = array();
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->languages = LanguageFactory::getInstance()->getLanguages();
	}
	
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['enable'])) $this->enable = intval($_POST['enable']);
		if (isset($_POST['languageIDs']) && is_array($_POST['languageIDs'])) $this->languageIDs = ArrayUtil::toIntegerArray($_POST['languageIDs']);
	}
	
	/**
	 * @see wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->enable == 1) {
			// add default language
			if (!in_array(LanguageFactory::getInstance()->getDefaultLanguageID(), $this->languageIDs)) {
				$this->languageIDs[] = LanguageFactory::getInstance()->getDefaultLanguageID();
			}

			// validate language ids
			$contentLanguages = 0;
			foreach ($this->languageIDs as $languageID) {
				if (isset($this->languages[$languageID])) {
					$contentLanguages++;
				}
			}
			
			if ($contentLanguages < 2) {
				throw new UserInputException('languageIDs');
			}
		}
	}
	
	/**
	 * @see wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();

		// save
		LanguageEditor::enableMultilingualism(($this->enable == 1 ? $this->languageIDs : array()));
		
		// clear cache
		CacheHandler::getInstance()->clear(WCF_DIR.'cache/', 'cache.languages.php');
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// default values
			$contentLanguages = 0;
			foreach ($this->languages as $languageID => $language) {
				if ($language->hasContent) {
					$contentLanguages++;
					$this->languageIDs[] = $languageID;
				}
			}
			
			// add default language
			if (!in_array(LanguageFactory::getInstance()->getDefaultLanguageID(), $this->languageIDs)) {
				$this->languageIDs[] = LanguageFactory::getInstance()->getDefaultLanguageID();
			}
			
			if ($contentLanguages > 1) {
				$this->enable = 1;
			}
		}
		
		$this->languageList = LanguageFactory::getInstance()->getLanguages();
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'enable' => $this->enable,
			'languageIDs' => $this->languageIDs,
			'languages' => $this->languageList
		));
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
