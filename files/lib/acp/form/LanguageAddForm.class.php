<?php
namespace wcf\acp\form;
use wcf\data\language\LanguageEditor;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Shows the language add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.acp.language
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class LanguageAddForm extends ACPForm {
	/**
	 * @see wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.add';
	
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.language.canAddLanguage');
	
	public $mode = 'import';
	public $languageFile = '';
	public $languageCode = '';
	public $sourceLanguageID = 0;
	public $filename = '';
	public $sourceLanguage, $language;
	public $importField = 'languageFile';
	public $languages = array();
	
	/**
	 * @see wcf\form\Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		// mode
		if (isset($_POST['mode'])) $this->mode = $_POST['mode'];
		
		// copy
		if (isset($_POST['languageCode'])) $this->languageCode = $_POST['languageCode'];
		if (isset($_POST['sourceLanguageID'])) $this->sourceLanguageID = intval($_POST['sourceLanguageID']);
		
		// import
		if (isset($_POST['languageFile']) && !empty($_POST['languageFile'])) {
			$this->languageFile = $_POST['languageFile'];
			$this->filename = $_POST['languageFile'];
		}
		if (isset($_FILES['languageUpload']) && !empty($_FILES['languageUpload']['tmp_name'])) {
			$this->importField = 'languageUpload';
			$this->filename = $_FILES['languageUpload']['tmp_name'];
		}
	}
	
	/**
	 * @see wcf\form\Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->mode == 'copy') {
			// language code
			if (empty($this->languageCode)) {
				throw new UserInputException('languageCode');
			}
			
			// 
			if (LanguageFactory::getInstance()->getLanguageByCode($this->languageCode)) {
				throw new UserInputException('languageCode', 'notUnique');
			}
			
			// source language id
			if (empty($this->sourceLanguageID)) {
				throw new UserInputException('sourceLanguageID');
			}
			
			// get language
			$this->sourceLanguage = LanguageFactory::getInstance()->getLanguage($this->sourceLanguageID);;
			if (!$this->sourceLanguage->languageID) {
				throw new UserInputException('sourceLanguageID');
			}
		}
		else {
			// check file
			if (!file_exists($this->filename)) {
				throw new UserInputException('languageFile');
			}
			
			// try to import
			try {
				// open xml document
				$xml = new XML();
				$xml->load($this->filename);
				
				// import xml document
				$this->language = LanguageEditor::importFromXML($xml, PACKAGE_ID);
			}
			catch (SystemException $e) {
				throw new UserInputException($this->importField, $e->getMessage());
			}
		}
	}
	
	/**
	 * @see wcf\form\Form::save()
	 */
	public function save() {
		parent::save();
		
		if ($this->mode == 'copy') {
			$this->language = LanguageEditor::create(array(
				'languageCode' => StringUtil::toLowerCase($this->languageCode)
			));
			$languageEditor = new LanguageEditor($this->sourceLanguage);
			$languageEditor->copy($this->language);
		}
		
		// add language to this package
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_language_to_package
			WHERE	languageID = ?
				AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->language->languageID,
			PACKAGE_ID
		));
		
		$row = $statement->fetchArray();
		if (!$row['count']) {
			$sql = "INSERT INTO	wcf".WCF_N."_language_to_package
						(languageID, packageID)
				VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->language->languageID,
				PACKAGE_ID
			));
		}
		
		LanguageFactory::getInstance()->clearCache();
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see wcf\page\Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->languages = LanguageFactory::getInstance()->getLanguages();
	}
	
	/**
	 * @see wcf\page\Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'mode' => $this->mode,
			'languageCode' => $this->languageCode,
			'sourceLanguageID' => $this->sourceLanguageID,
			'languages' => $this->languages,
			'languageFile' => $this->languageFile
		));
	}
	
	/**
	 * @see wcf\page\Page::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
