<?php
namespace wcf\acp\form;
use wcf\data\tag\Tag;
use wcf\data\tag\TagAction;
use wcf\data\tag\TagEditor;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the tag add form.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class TagAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.tag.add';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.tag.canManageTag');
	
	/**
	 * @see	wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_TAGGING');
	
	/**
	 * list of available languages
	 * @var	array
	 */
	public $availableLanguages = array();
	
	/**
	 * name value
	 * @var	string
	 */
	public $name = '';
	
	/**
	 * language value
	 * @var	string
	 */
	public $languageID = 0;
	
	/**
	 * synonyms
	 * @var	array<string>
	 */
	public $synonyms = array();
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->availableLanguages = LanguageFactory::getInstance()->getContentLanguages();
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['name'])) $this->name = StringUtil::trim($_POST['name']);
		if (isset($_POST['languageID'])) $this->languageID = intval($_POST['languageID']);
		
		// actually these are synonyms
		if (isset($_POST['tags']) && is_array($_POST['tags'])) $this->synonyms = ArrayUtil::trim($_POST['tags']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->name)) {
			throw new UserInputException('name');
		}
		
		// validate language
		if (!isset($this->tagObj)) {
			if (empty($this->availableLanguages)) {
				// force default language id
				$this->languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
			}
			else {
				if (!isset($this->availableLanguages[$this->languageID])) {
					throw new UserInputException('languageID', 'notFound');
				}
			}
		}
		
		// check for duplicates
		$tag = Tag::getTag($this->name, $this->languageID);
		if ($tag !== null && (!isset($this->tagObj) || $tag->tagID != $this->tagObj->tagID)) {
			throw new UserInputException('name', 'duplicate');
		}
		
		// validate synonyms
		foreach ($this->synonyms as $key => $synonym) {
			if (mb_strtolower($synonym) == mb_strtolower($this->name)) {
				unset($this->synonyms[$key]);
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			// pre-select default language id
			if (!empty($this->availableLanguages)) {
				$this->languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
				if (!isset($this->availableLanguages[$this->languageID])) {
					// language id is not within content languages, try user's language instead
					$this->languageID = WCF::getUser()->languageID;
					if (!isset($this->availableLanguages[$this->languageID])) {
						// this installation is weird, just select nothing
						$this->languageID = 0;
					}
				}
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save tag
		$this->objectAction = new TagAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'name' => $this->name,
			'languageID' => $this->languageID
		))));
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		$editor = new TagEditor($returnValues['returnValues']);
		
		foreach ($this->synonyms as $synonym) {
			if (empty($synonym)) continue;
			
			// find existing tag
			$synonymObj = Tag::getTag($synonym, $this->languageID);
			if ($synonymObj === null) {
				$synonymAction = new TagAction(array(), 'create', array('data' => array(
					'name' => $synonym,
					'languageID' => $this->languageID,
					'synonymFor' => $editor->tagID
				)));
				$synonymAction->executeAction();
			}
			else {
				$editor->addSynonym($synonymObj);
			}
		}
		
		$this->saved();
		
		// reset values
		$this->name = '';
		$this->synonyms = array();
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'availableLanguages' => $this->availableLanguages,
			'name' => $this->name,
			'languageID' => $this->languageID,
			'synonyms' => $this->synonyms
		));
	}
}
