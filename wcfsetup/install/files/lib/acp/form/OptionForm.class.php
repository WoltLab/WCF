<?php
namespace wcf\acp\form;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\OptionAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\StringUtil;

/**
 * Shows the option edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class OptionForm extends AbstractOptionListForm {
	/**
	 * category option
	 * @var	\wcf\data\option\category\OptionCategory
	 */
	public $category = null;
	
	/**
	 * category id
	 * @var	integer
	 */
	public $categoryID = 0;
	
	/**
	 * option name for highlighting
	 * @var	string
	 */
	public $optionName = '';
	
	/**
	 * the option tree
	 * @var	array
	 */
	public $optionTree = array();
	
	/**
	 * @see	\wcf\acp\form\AbstractOptionListForm::$languageItemPattern
	 */
	protected $languageItemPattern = 'wcf.acp.option.option\d+';
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->categoryID = intval($_REQUEST['id']);
		$this->category = new OptionCategory($this->categoryID);
		if (!$this->category->categoryID) {
			throw new IllegalLinkException();
		}
		$this->categoryName = $this->category->categoryName;
		
		if (isset($_GET['optionName'])) $this->optionName = StringUtil::trim($_GET['optionName']);
		
		parent::readParameters();
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save options
		$saveOptions = $this->optionHandler->save('wcf.acp.option', 'wcf.acp.option.option');
		$this->objectAction = new OptionAction(array(), 'updateAll', array('data' => $saveOptions));
		$this->objectAction->executeAction();
		$this->saved();
		
		// reset styles to make sure the updated option values are used
		StyleHandler::resetStylesheets();
		
		// show succes message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// load option tree
		$this->optionTree = $this->optionHandler->getOptionTree($this->category->categoryName);
		
		if (empty($_POST)) {
			// not a valid top (level 1 or 2) category
			if (!isset($this->optionTree[0])) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'category' => $this->category,
			'optionName' => $this->optionName,
			'optionTree' => $this->optionTree
		));
	}
	
	/**
	 * @see	\wcf\form\IForm::show()
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
		
		// show form
		parent::show();
	}
}
