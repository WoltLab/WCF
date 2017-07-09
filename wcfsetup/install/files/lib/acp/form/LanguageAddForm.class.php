<?php
namespace wcf\acp\form;
use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the language add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class LanguageAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language';
	
	/**
	 * country code
	 * @var	string
	 */
	public $countryCode = '';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.language.canManageLanguage'];
	
	/**
	 * language object
	 * @var	Language
	 */
	public $language = null;
	
	/**
	 * language name
	 * @var	string
	 */
	public $languageName = '';
	
	/**
	 * language code
	 * @var	string
	 */
	public $languageCode = '';
	
	/**
	 * list of available languages
	 * @var	Language[]
	 */
	public $languages = [];
	
	/**
	 * parent language id
	 * @var	integer
	 */
	public $parentID = 0;
	
	/**
	 * parent language object
	 * @var	Language
	 */
	public $parentLanguage = null;
	
	/**
	 * source language object
	 * @var	Language
	 */
	public $sourceLanguage = null;
	
	/**
	 * source language id
	 * @var	integer
	 */
	public $sourceLanguageID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['countryCode'])) $this->countryCode = StringUtil::trim($_POST['countryCode']);
		if (isset($_POST['languageName'])) $this->languageName = StringUtil::trim($_POST['languageName']);
		if (isset($_POST['languageCode'])) $this->languageCode = StringUtil::trim($_POST['languageCode']);
		if (isset($_POST['sourceLanguageID'])) $this->sourceLanguageID = intval($_POST['sourceLanguageID']);
		if (isset($_POST['parentID'])) $this->parentID = intval($_POST['parentID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// language name
		if (empty($this->languageName)) {
			throw new UserInputException('languageName');
		}
		
		// country code
		if (empty($this->countryCode)) {
			throw new UserInputException('countryCode');
		}
		
		// language code
		$this->validateLanguageCode();
		
		// source language id
		$this->validateSource();
		
		// parent language id
		$this->validateParent();
	}
	
	/**
	 * Validates the language code.
	 */
	protected function validateLanguageCode() {
		if (empty($this->languageCode)) {
			throw new UserInputException('languageCode');
		}
		if (LanguageFactory::getInstance()->getLanguageByCode($this->languageCode)) {
			throw new UserInputException('languageCode', 'notUnique');
		}
	}
	
	/**
	 * Validates given source language.
	 */
	protected function validateSource() {
		if (empty($this->sourceLanguageID)) {
			throw new UserInputException('sourceLanguageID');
		}
		
		// get language
		$this->sourceLanguage = LanguageFactory::getInstance()->getLanguage($this->sourceLanguageID);
		if (!$this->sourceLanguage->languageID) {
			throw new UserInputException('sourceLanguageID');
		}
	}
	
	/**
	 * Validates given parent language.
	 */
	protected function validateParent() {
		if (empty($this->parentID)) return;
		
		// get language
		$this->parentLanguage = LanguageFactory::getInstance()->getLanguage($this->parentID);
		if (!$this->parentLanguage->languageID) {
			throw new UserInputException('parentID');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->language = LanguageEditor::create([
			'countryCode' => mb_strtolower($this->countryCode),
			'languageName' => $this->languageName,
			'languageCode' => mb_strtolower($this->languageCode),
			'parentID' => $this->parentID ?: null
		]);
		$languageEditor = new LanguageEditor($this->sourceLanguage);
		$languageEditor->copy($this->language);
		
		// copy content
		LanguageEditor::copyLanguageContent($this->sourceLanguage->languageID, $this->language->languageID);
		
		// reset caches
		LanguageFactory::getInstance()->clearCache();
		LanguageFactory::getInstance()->deleteLanguageCache();
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
		
		// reset values
		$this->countryCode = $this->languageCode = $this->languageName = '';
		$this->sourceLanguageID = $this->parentID = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->languages = LanguageFactory::getInstance()->getLanguages();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'countryCode' => $this->countryCode,
			'languageName' => $this->languageName,
			'languageCode' => $this->languageCode,
			'sourceLanguageID' => $this->sourceLanguageID,
			'languages' => $this->languages,
			'parentID' => $this->parentID,
			'parentLanguage' => $this->parentLanguage,
			'action' => 'add'
		]);
	}
}
