<?php
namespace wcf\acp\form;
use wcf\data\category\Category;
use wcf\data\category\CategoryNodeTree;
use wcf\data\smiley\SmileyAction;
use wcf\data\smiley\SmileyEditor;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Shows the smiley add form.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class SmileyAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.smiley.add';
	
	/**
	 * @see	\wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'smileyAdd';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.content.smiley.canManageSmiley');
	
	/**
	 * primary smiley code
	 * @var	string
	 */
	public $smileyCode = '';
	
	/**
	 * showorder value
	 * @var	integer
	 */
	public $showOrder = 0;
	
	/**
	 * categoryID value
	 * @var	integer
	 */
	public $categoryID = 0;
	
	/**
	 * smileyTitle
	 * @var	string
	 */
	public $smileyTitle = '';
	
	/**
	 * aliases value
	 * @var	string
	 */
	public $aliases = '';
	
	/**
	 * path to the smiley file
	 * @var	string
	 */
	public $smileyPath = '';
	
	/**
	 * node tree with available smiley categories
	 * @var	\wcf\data\category\CategoryNodeTree
	 */
	public $categoryNodeTree = null;
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		I18nHandler::getInstance()->assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'smileyTitle' => $this->smileyTitle,
			'showOrder' => $this->showOrder,
			'categoryID' => $this->categoryID,
			'smileyCode' => $this->smileyCode,
			'aliases' => $this->aliases,
			'smileyPath' => $this->smileyPath,
			'categoryNodeList' => $this->categoryNodeTree->getIterator()
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->categoryNodeTree = new CategoryNodeTree('com.woltlab.wcf.bbcode.smiley', 0, true);
	}
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		I18nHandler::getInstance()->register('smileyTitle');
	}
	
	/**
	 * @see	\wcf\page\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		I18nHandler::getInstance()->readValues();
		
		if (I18nHandler::getInstance()->isPlainValue('smileyTitle')) $this->smileyTitle = I18nHandler::getInstance()->getValue('smileyTitle');
		
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['categoryID'])) $this->categoryID = intval($_POST['categoryID']);
		if (isset($_POST['smileyCode'])) $this->smileyCode = StringUtil::trim($_POST['smileyCode']);
		if (isset($_POST['aliases'])) $this->aliases = StringUtil::unifyNewlines(StringUtil::trim($_POST['aliases']));
		if (isset($_POST['smileyPath'])) $this->smileyPath = FileUtil::removeLeadingSlash(StringUtil::trim($_POST['smileyPath']));
	}
	
	/**
	 * @see	\wcf\page\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new SmileyAction(array(), 'create', array(
			'data' => array_merge($this->additionalFields, array(
				'smileyTitle' => $this->smileyTitle,
				'smileyCode' => $this->smileyCode,
				'aliases' => $this->aliases,
				'smileyPath' => $this->smileyPath,
				'showOrder' => $this->showOrder,
				'categoryID' => $this->categoryID ?: null,
				'packageID' => 1
			))
		));
		$this->objectAction->executeAction();
		$returnValues = $this->objectAction->getReturnValues();
		$smileyEditor = new SmileyEditor($returnValues['returnValues']);
		$smileyID = $returnValues['returnValues']->smileyID;
		
		if (!I18nHandler::getInstance()->isPlainValue('smileyTitle')) {
			I18nHandler::getInstance()->save('smileyTitle', 'wcf.smiley.title'.$smileyID, 'wcf.smiley', 1);
			
			// update title
			$smileyEditor->update(array(
				'smileyTitle' => 'wcf.smiley.title'.$smileyID
			));
		}
		
		// reset values
		$this->smileyCode = '';
		$this->categoryID = 0;
		$this->showOrder = 0;
		$this->smileyPath = '';
		$this->aliases = '';
		
		I18nHandler::getInstance()->reset();
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see	\wcf\page\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate title
		if (!I18nHandler::getInstance()->validateValue('smileyTitle')) {
			if (I18nHandler::getInstance()->isPlainValue('smileyTitle')) {
				throw new UserInputException('smileyTitle');
			}
			else {
				throw new UserInputException('smileyTitle', 'multilingual');
			}
		}
		
		if ($this->categoryID) {
			$category = new Category($this->categoryID);
			if (!$category->categoryID) {
				throw new UserInputException('categoryID', 'notValid');
			}
		}
		
		if (empty($this->smileyCode)) {
			throw new UserInputException('smileyCode');
		}
		
		if (empty($this->smileyPath)) {
			throw new UserInputException('smileyPath');
		}
		
		if (!is_file(WCF_DIR.$this->smileyPath)) {
			throw new UserInputException('smileyPath', 'notFound');
		}
		
		// validate smiley code and aliases against existing smilies
		$conditionBuilder = new PreparedStatementConditionBuilder();
		if (isset($this->smiley)) {
			$conditionBuilder->add('smileyID <> ?', array($this->smiley->smileyID));
		}
		$sql = "SELECT	smileyCode, aliases
			FROM	wcf".WCF_N."_smiley
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		$aliases = explode("\n", $this->aliases);
		while ($row = $statement->fetchArray()) {
			$known = array();
			if (!empty($row['aliases'])) {
				$known = explode("\n", $row['aliases']);
			}
			$known[] = $row['smileyCode'];
			
			if (in_array($this->smileyCode, $known)) {
				throw new UserInputException('smileyCode', 'notUnique');
			}
			else {
				$conflicts = array_intersect($aliases, $known);
				if (!empty($conflicts)) {
					throw new UserInputException('aliases', 'notUnique');
				}
			}
		}
	}
}
