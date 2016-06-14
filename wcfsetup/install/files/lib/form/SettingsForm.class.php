<?php
namespace wcf\form;
use wcf\data\language\Language;
use wcf\data\style\Style;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\UserAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\menu\user\UserMenu;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Shows the dynamic options edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Form
 */
class SettingsForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * user option handler
	 * @var	\wcf\system\option\user\UserOptionHandler
	 */
	public $optionHandler = null;
	
	/**
	 * @inheritDoc
	 */
	public $errorType = [];
	
	/**
	 * option category
	 * @var	string
	 */
	public $category = 'general';
	
	/**
	 * list of available content languages
	 * @var	Language[]
	 */
	public $availableContentLanguages = [];
	
	/**
	 * list of available languages
	 * @var	Language[]
	 */
	public $availableLanguages = [];
	
	/**
	 * list of available styles
	 * @var	Style[]
	 */
	public $availableStyles = [];
	
	/**
	 * list of content language ids
	 * @var	integer[]
	 */
	public $contentLanguageIDs = [];
	
	/**
	 * language id
	 * @var	integer
	 */
	public $languageID = 0;
	
	/**
	 * style id
	 * @var	integer
	 */
	public $styleID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['category'])) {
			$this->category = $_REQUEST['category'];
			
			// validate category
			if (UserOptionCategory::getCategoryByName('settings.'.$this->category) === null) {
				throw new IllegalLinkException();
			}
		}
		
		$this->optionHandler = new UserOptionHandler(false, '', 'settings.'.$this->category);
		$this->optionHandler->setUser(WCF::getUser());
		
		if ($this->category == 'general') {
			$this->availableContentLanguages = LanguageFactory::getInstance()->getContentLanguages();
			$this->availableLanguages = LanguageFactory::getInstance()->getLanguages();
			$this->availableStyles = StyleHandler::getInstance()->getAvailableStyles();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->optionHandler->readUserInput($_POST);
		
		// static options
		if ($this->category == 'general') {
			if (isset($_POST['contentLanguageIDs']) && is_array($_POST['contentLanguageIDs'])) $this->contentLanguageIDs = ArrayUtil::toIntegerArray($_POST['contentLanguageIDs']);
			if (isset($_POST['languageID'])) $this->languageID = intval($_POST['languageID']);
			if (isset($_POST['styleID'])) $this->styleID = intval($_POST['styleID']);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// dynamic options
		$optionErrors = $this->optionHandler->validate();
		if (!empty($optionErrors)) {
			$this->errorType = $optionErrors;
			throw new UserInputException('options', $this->errorType);
		}
		
		// static options
		if ($this->category == 'general') {
			// validate language id
			if (!isset($this->availableLanguages[$this->languageID])) {
				$this->languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
			}
			
			// validate content language ids
			foreach ($this->contentLanguageIDs as $key => $languageID) {
				if (!isset($this->availableContentLanguages[$languageID])) {
					unset($this->contentLanguageIDs[$key]);
				}
			}
			
			if (empty($this->contentLanguageIDs) && isset($this->availableContentLanguages[$this->languageID])) {
				$this->contentLanguageIDs[] = $this->languageID;
			}
			
			// validate style id
			if (!isset($this->availableStyles[$this->styleID])) {
				$this->styleID = 0;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// default values
		if (empty($_POST)) {
			// static options
			if ($this->category == 'general') {
				$this->contentLanguageIDs = WCF::getUser()->getLanguageIDs();
				if (isset($this->availableLanguages[WCF::getUser()->languageID])) {
					$this->languageID = WCF::getUser()->languageID;
				}
				$this->styleID = WCF::getUser()->styleID;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		$saveOptions = $this->optionHandler->save();
		$parameters = ['options' => $saveOptions];
		// static options
		if ($this->category == 'general') {
			$parameters['data'] = array_merge($this->additionalFields, [
				'languageID' => $this->languageID,
				'styleID' => $this->styleID
			]);
			$parameters['languageIDs'] = $this->contentLanguageIDs;
		}
		
		$this->objectAction = new UserAction([WCF::getUser()], 'update', $parameters);
		$this->objectAction->executeAction();
		
		// static options
		if ($this->category == 'general') {
			// reset user language ids cache
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'languageIDs');
		}
		$this->saved();
		
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'optionTree' => $this->optionHandler->getOptionTree(),
			'category' => $this->category
		]);
		// static options
		if ($this->category == 'general') {
			WCF::getTPL()->assign([
				'availableContentLanguages' => $this->availableContentLanguages,
				'availableLanguages' => $this->availableLanguages,
				'availableStyles' => $this->availableStyles,
				'contentLanguageIDs' => $this->contentLanguageIDs,
				'languageID' => $this->languageID,
				'styleID' => $this->styleID
			]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.option.category.settings.'.$this->category);
		
		parent::show();
	}
}
