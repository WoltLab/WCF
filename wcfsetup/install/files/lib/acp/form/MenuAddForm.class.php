<?php
namespace wcf\acp\form;
use wcf\data\box\Box;
use wcf\data\menu\MenuAction;
use wcf\data\menu\MenuEditor;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the menu add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 * @since	2.2k
 */
class MenuAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.cms.canManageMenu'];
	
	/**
	 * menu title
	 * @var	string
	 */
	public $title = '';
	
	/**
	 * box position
	 * @var	string
	 */
	public $position = '';
	
	/**
	 * show order
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * true if created box is visible everywhere
	 * @var	boolean
	 */
	public $visibleEverywhere = 1;
	
	/**
	 * css class name of created box
	 * @var	string
	 */
	public $cssClassName = '';
	
	/**
	 * true if box header is visible
	 * @var	boolean
	 */
	public $showHeader = 1;
	
	/**
	 * page ids
	 * @var	integer[]
	 */
	public $pageIDs = [];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
	
		I18nHandler::getInstance()->register('title');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('title')) $this->title = I18nHandler::getInstance()->getValue('title');
		
		$this->visibleEverywhere = $this->showHeader = $this->showOrder = 0;
		if (isset($_POST['position'])) $this->position = $_POST['position'];
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['visibleEverywhere'])) $this->visibleEverywhere = intval($_POST['visibleEverywhere']);
		if (isset($_POST['cssClassName'])) $this->cssClassName = StringUtil::trim($_POST['cssClassName']);
		if (isset($_POST['showHeader'])) $this->showHeader = intval($_POST['showHeader']);
		if (isset($_POST['pageIDs']) && is_array($_POST['pageIDs'])) $this->pageIDs = ArrayUtil::toIntegerArray($_POST['pageIDs']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// validate menu title
		if (!I18nHandler::getInstance()->validateValue('title')) {
			if (I18nHandler::getInstance()->isPlainValue('title')) {
				throw new UserInputException('title');
			}
			else {
				throw new UserInputException('title', 'multilingual');
			}
		}
		
		// validate box position
		$this->validatePosition();
		
		// validate page ids
		if (!empty($this->pageIDs)) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('pageID IN (?)', [$this->pageIDs]);
			$sql = "SELECT  pageID
				FROM    wcf".WCF_N."_page
				" . $conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
			$this->pageIDs = [];
			while ($row = $statement->fetchArray()) {
				$this->pageIDs[] = $row['pageID'];
			}
		}
	}
	
	/**
	 * Validates box position.
	 * 
	 * @throws	UserInputException
	 */
	protected function validatePosition() {
		if (!in_array($this->position, Box::$availablePositions)) {
			throw new UserInputException('position');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save label
		$this->objectAction = new MenuAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'title' => $this->title,
			'packageID' => 1,
			'identifier' => ''
		)), 'boxData' => array(
			'name' => $this->title,
			'boxType' => 'menu',
			'position' => $this->position,
			'visibleEverywhere' => ($this->visibleEverywhere) ? 1 : 0,
			'showHeader' => ($this->showHeader) ? 1 : 0,
			'showOrder' => $this->showOrder,
			'cssClassName' => $this->cssClassName,
			'packageID' => 1
		), 'pageIDs' => $this->pageIDs));
		$returnValues = $this->objectAction->executeAction();
		// set generic identifier
		$menuEditor = new MenuEditor($returnValues['returnValues']);
		$menuEditor->update(array(
			'identifier' => 'com.woltlab.wcf.genericMenu'.$menuEditor->menuID
		));
		// save i18n
		if (!I18nHandler::getInstance()->isPlainValue('title')) {
			I18nHandler::getInstance()->save('title', 'wcf.menu.menu'.$menuEditor->menuID, 'wcf.menu', 1);
				
			// update title
			$menuEditor->update(array(
				'title' => 'wcf.menu.menu'.$menuEditor->menuID
			));
		}
		$this->saved();
		
		// reset values
		$this->title = '';
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
		
		I18nHandler::getInstance()->reset();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'title' => 'title',
			'position' => $this->position,
			'cssClassName' => $this->cssClassName,
			'showOrder' => $this->showOrder,
			'visibleEverywhere' => $this->visibleEverywhere,
			'showHeader' => $this->showHeader,
			'pageIDs' => $this->pageIDs,
			'availablePositions' => Box::$availableMenuPositions,
			'pageNodeList' => (new PageNodeTree())->getNodeList()
		));
	}
}
