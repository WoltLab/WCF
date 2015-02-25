<?php
namespace wcf\acp\form;
use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows the language edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class LanguageEditForm extends LanguageAddForm {
	/**
	 * language id
	 * @var	integer
	 */
	public $languageID = 0;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->languageID = intval($_REQUEST['id']);
		$this->language = new Language($this->languageID);
		if (!$this->language->languageID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	\wcf\acp\form\LanguageAddForm::validateLanguageCode()
	 */
	protected function validateLanguageCode() {
		if ($this->language->languageCode != mb_strtolower($this->languageCode)) {
			parent::validateLanguageCode();
		}
	}
	
	/**
	 * @see	\wcf\acp\form\LanguageAddForm::validateSource()
	 */
	protected function validateSource() {}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		AbstractForm::save();
		
		$editor = new LanguageEditor($this->language);
		$editor->update(array(
			'countryCode' => mb_strtolower($this->countryCode),
			'languageName' => $this->languageName,
			'languageCode' => mb_strtolower($this->languageCode)		
		));
		LanguageFactory::getInstance()->clearCache();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->countryCode = $this->language->countryCode;
			$this->languageName = $this->language->languageName;
			$this->languageCode = $this->language->languageCode;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languageID' => $this->languageID,
			'language' => $this->language,
			'action' => 'edit'
		));
	}
}
