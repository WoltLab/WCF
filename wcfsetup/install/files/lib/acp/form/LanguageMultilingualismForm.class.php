<?php
namespace wcf\acp\form;
use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\form\AbstractForm;
use wcf\system\cache\builder\LanguageCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\ArrayUtil;

/**
 * Shows the language multilingualism form.
 * 
 * @author	Jean-Marc Licht
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class LanguageMultilingualismForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.multilingualism';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.language.canManageLanguage'];
	
	/**
	 * indicates if multilingualism is enabled
	 * @var	integer
	 */
	public $enable = 0;
	
	/**
	 * ids of selected available languages
	 * @var	integer[]
	 */
	public $languageIDs = [];
	
	/**
	 * list of available content languages
	 * @var	Language[]
	 */
	public $languages = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->languages = LanguageFactory::getInstance()->getLanguages();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['enable'])) $this->enable = intval($_POST['enable']);
		if (isset($_POST['languageIDs']) && is_array($_POST['languageIDs'])) $this->languageIDs = ArrayUtil::toIntegerArray($_POST['languageIDs']);
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save
		LanguageEditor::enableMultilingualism(($this->enable == 1 ? $this->languageIDs : []));
		
		// clear cache
		LanguageCacheBuilder::getInstance()->reset();
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
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
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'defaultLanguageID' => LanguageFactory::getInstance()->getDefaultLanguageID(),
			'enable' => $this->enable,
			'languageIDs' => $this->languageIDs,
			'languages' => $this->languages
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
