<?php
namespace wcf\acp\form;
use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\XML;

/**
 * Shows the language import form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class LanguageImportForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.import';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.language.canManageLanguage'];
	
	/**
	 * file name
	 * @var	string
	 */
	public $filename = '';
	
	/**
	 * language object
	 * @var	Language
	 */
	public $language;
	
	/**
	 * list of available languages
	 * @var	Language[]
	 */
	public $languages = [];
	
	/**
	 * source language object
	 * @var	Language
	 */
	public $sourceLanguage;
	
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
		
		if (isset($_FILES['languageUpload']) && !empty($_FILES['languageUpload']['tmp_name'])) {
			$this->filename = $_FILES['languageUpload']['tmp_name'];
		}
		if (isset($_POST['sourceLanguageID'])) $this->sourceLanguageID = intval($_POST['sourceLanguageID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// check file
		if (!file_exists($this->filename)) {
			throw new UserInputException('languageUpload');
		}
		
		if (empty($this->sourceLanguageID)) {
			throw new UserInputException('sourceLanguageID');
		}
		
		// get language
		$this->sourceLanguage = LanguageFactory::getInstance()->getLanguage($this->sourceLanguageID);
		if (!$this->sourceLanguage->languageID) {
			throw new UserInputException('sourceLanguageID');
		}
		
		// try to import
		try {
			// open xml document
			$xml = new XML();
			$xml->load($this->filename);
			
			// import xml document
			$this->language = LanguageEditor::importFromXML($xml, -1, $this->sourceLanguage);
		}
		catch (SystemException $e) {
			throw new UserInputException('languageUpload', $e->getMessage());
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
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
			'languages' => $this->languages,
			'sourceLanguageID' => $this->sourceLanguageID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
