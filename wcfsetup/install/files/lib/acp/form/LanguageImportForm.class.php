<?php
namespace wcf\acp\form;
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
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class LanguageImportForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.import';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.language.canManageLanguage');
	
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
	 * @var	\wcf\data\language\Language
	 */
	public $language = null;
	
	/**
	 * import language file
	 * @var	string
	 */
	public $languageFile = '';
	
	/**
	 * list of available languages
	 * @var	array<\wcf\data\language\Language>
	 */
	public $languages = array();
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
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
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
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
			$this->language = LanguageEditor::importFromXML($xml, -1);
		}
		catch (SystemException $e) {
			throw new UserInputException($this->importField, $e->getMessage());
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
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
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languages' => $this->languages,
			'languageFile' => $this->languageFile
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
