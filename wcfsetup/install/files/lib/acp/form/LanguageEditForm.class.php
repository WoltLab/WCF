<?php
namespace wcf\acp\form;
use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows the language edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class LanguageEditForm extends LanguageAddForm {
	/**
	 * language id
	 * @var	integer
	 */
	public $languageID = 0;
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	protected function validateLanguageCode() {
		if ($this->language->languageCode != mb_strtolower($this->languageCode)) {
			parent::validateLanguageCode();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function validateSource() {}
	
	/**
	 * @inheritDoc
	 */
	protected function validateParent() {
		parent::validateParent();
		
		if ($this->languageID == $this->parentID) {
			throw new UserInputException('parentID', 'parentsItself');
		}
		
		$language = $this->language;
		while ($language = LanguageFactory::getInstance()->getLanguage($language->languageID)) {
			if ($language->languageID == $this->parentID) {
				throw new UserInputException('parentID', 'parentsItself');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$editor = new LanguageEditor($this->language);
		$editor->update([
			'countryCode' => mb_strtolower($this->countryCode),
			'languageName' => $this->languageName,
			'languageCode' => mb_strtolower($this->languageCode),
			'parentID' => $this->parentID
		]);
		LanguageFactory::getInstance()->clearCache();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			$this->countryCode = $this->language->countryCode;
			$this->languageName = $this->language->languageName;
			$this->languageCode = $this->language->languageCode;
			$this->parentID = $this->language->parentID;
			
			if ($this->parentID) $this->parentLanguage = LanguageFactory::getInstance()->getLanguage($this->parentID);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'languageID' => $this->languageID,
			'language' => $this->language,
			'action' => 'edit'
		]);
	}
}
