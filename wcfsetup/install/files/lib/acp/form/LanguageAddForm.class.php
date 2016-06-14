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
 * @copyright	2001-2016 WoltLab GmbH
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
	 * @var	\wcf\data\language\Language
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
	 * source language object
	 * @var	\wcf\data\language\Language
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
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$this->language = LanguageEditor::create([
			'countryCode' => mb_strtolower($this->countryCode),
			'languageName' => $this->languageName,
			'languageCode' => mb_strtolower($this->languageCode)
		]);
		$languageEditor = new LanguageEditor($this->sourceLanguage);
		$languageEditor->copy($this->language);
		
		LanguageFactory::getInstance()->clearCache();
		LanguageFactory::getInstance()->deleteLanguageCache();
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
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
			'action' => 'add'
		]);
	}
}
