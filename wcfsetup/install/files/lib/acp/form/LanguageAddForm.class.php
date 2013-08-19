<?php
namespace wcf\acp\form;
use wcf\data\language\LanguageEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
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
	public $activeMenuItem = 'wcf.acp.menu.link.language';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.language.canManageLanguage');
	
	/**
	 * language object
	 * @var	wcf\data\language\Language
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
	 * @var	array<wcf\data\language\Language>
	 */
	public $languages = array();
	
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
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['languageName'])) $this->languageName = StringUtil::trim($_POST['languageName']);
		if (isset($_POST['languageCode'])) $this->languageCode = StringUtil::trim($_POST['languageCode']);
		if (isset($_POST['sourceLanguageID'])) $this->sourceLanguageID = intval($_POST['sourceLanguageID']);
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// language name
		if (empty($this->languageName)) {
			throw new UserInputException('languageName');
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
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->language = LanguageEditor::create(array(
			'languageName' => $this->languageName,
			'languageCode' => mb_strtolower($this->languageCode)
		));
		$languageEditor = new LanguageEditor($this->sourceLanguage);
		$languageEditor->copy($this->language);
		LanguageFactory::getInstance()->clearCache();
		LanguageFactory::getInstance()->deleteLanguageCache();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->languages = LanguageFactory::getInstance()->getLanguages();
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languageName' => $this->languageName,
			'languageCode' => $this->languageCode,
			'sourceLanguageID' => $this->sourceLanguageID,
			'languages' => $this->languages,
			'action' => 'add'
		));
	}
}
