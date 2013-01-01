<?php
namespace wcf\acp\form;
use wcf\data\language\LanguageEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\SystemException;
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
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class LanguageAddForm extends AbstractForm {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.add';
	
	/**
	 * file name
	 * @var	string
	 */
	public $filename = '';
	
	/**
	 * import field
	 * @var	string
	 */
	public $importField = 'languageFile';
	
	/**
	 * language object
	 * @var	wcf\data\language\Language
	 */
	public $language = null;
	
	/**
	 * language code
	 * @var	string
	 */
	public $languageCode = '';
	
	/**
	 * import language file
	 * @var	string
	 */
	public $languageFile = '';
	
	/**
	 * list of available languages
	 * @var	array<wcf\data\language\Language>
	 */
	public $languages = array();
	
	/**
	 * mode
	 * @var	string
	 */
	public $mode = 'import';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.language.canAddLanguage');
	
	/**
	 * source language object
	 * @var	wcf\data\language\Language
	 */
	public $sourceLanguage = null;
	
	/**
	 * source language id
	 * @var	integer
	 */
	public $sourceLanguageID = 0;
	
	/**
	 * @see	wcf\form\Form::readFormParameters()
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
	 * @see	wcf\form\Form::validate()
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
			$this->sourceLanguage = LanguageFactory::getInstance()->getLanguage($this->sourceLanguageID);
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
	 * @see	wcf\form\Form::save()
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
		
		LanguageFactory::getInstance()->clearCache();
		LanguageFactory::getInstance()->deleteLanguageCache();
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	wcf\page\Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->languages = LanguageFactory::getInstance()->getLanguages();
	}
	
	/**
	 * @see	wcf\page\Page::assignVariables()
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
	 * @see	wcf\page\Page::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
