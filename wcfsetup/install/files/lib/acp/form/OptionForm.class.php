<?php
namespace wcf\acp\form;
use wcf\system\menu\acp\ACPMenu;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\OptionAction;
use wcf\data\option\Option;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * Shows the option edit form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class OptionForm extends AbstractOptionListForm {
	/**
	 * @see wcf\page\AbstractPage::$templateName;
	 */
	public $templateName = 'option';
	
	/**
	 * category option
	 * @var OptionCategory
	 */
	public $category = null;
	
	/**
	 * category id
	 * @var integer
	 */
	public $categoryID = 0;
	
	/**
	 * active tab menu item name
	 * @var string
	 */
	public $activeTabMenuItem = '';
	
	/**
	 * the option tree
	 * @var array
	 */
	public $optionTree = array();
	
	/**
	 * @see	wcf\acp\form\AbstractOptionListForm::$languageItemPattern
	 */
	protected $languageItemPattern = 'wcf.option.option\d+';
	
	/**
	 * @see wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['categoryID'])) $this->categoryID = intval($_REQUEST['categoryID']);
		$this->category = new OptionCategory($this->categoryID);
		if (!isset($this->category->categoryID)) {
			throw new IllegalLinkException();
		}
		$this->categoryName = $this->category->categoryName;
	}
	
	/**
	 * @see wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['activeTabMenuItem'])) $this->activeTabMenuItem = $_POST['activeTabMenuItem'];
	}
	
	/**
	 * @see wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save options
		$saveOptions = array();
		foreach ($this->options as $option) {
			// handle i18n support
			if ($option->supportI18n) {
				if (I18nHandler::getInstance()->isPlainValue($option->optionName)) {
					I18nHandler::getInstance()->remove('wcf.option.option' . $option->optionID, $option->packageID);
					$saveOptions[$option->optionID] = I18nHandler::getInstance()->getValue($option->optionName);
				}
				else {
					I18nHandler::getInstance()->save($option->optionName, 'wcf.option.option' . $option->optionID, 'wcf.option', $option->packageID);
					$saveOptions[$option->optionID] = 'wcf.option.option' . $option->optionID;
				}
			}
			else {
				$saveOptions[$option->optionID] = $this->optionValues[$option->optionName];
			}
		}
		$optionAction = new OptionAction(array(), 'updateAll', array('data' => $saveOptions));
		$optionAction->executeAction();
		$this->saved();
		
		// show succes message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// get option values
			foreach ($this->options as $option) {
				$this->optionValues[$option->optionName] = $option->optionValue;
			}
		}
		
		// load option tree
		$this->optionTree = $this->getOptionTree($this->category->categoryName);
		
		if (!count($_POST)) {
			$this->activeTabMenuItem = $this->optionTree[0]['object']->categoryName;
		}
	}
	
	/**
	 * @see wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'category' => $this->category,
			'optionTree' => $this->optionTree,
			'activeTabMenuItem' => $this->activeTabMenuItem
		));
	}
	
	/**
	 * @see wcf\form\IForm::show()
	 */
	public function show() {
		// set active menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.option.category.'.$this->category->categoryName);
		
		// check permission
		WCF::getSession()->checkPermissions(array('admin.system.canEditOption'));

		if ($this->category->categoryName == 'module') {
			// check master password
			WCFACP::checkMasterPassword();
		}
		
		// get options and categories from cache
		$this->readCache();
		
		// show form
		parent::show();
	}
	
	/**
	 * @see wcf\acp\form\AbstractOptionListForm::checkOption()
	 */
	protected static function checkOption(Option $option) {
		if (!parent::checkOption($option)) return false;
		return ($option->hidden != 1);
	}
}
