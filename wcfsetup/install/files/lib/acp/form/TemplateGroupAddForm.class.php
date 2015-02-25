<?php
namespace wcf\acp\form;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupAction;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Shows the form for adding new template groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class TemplateGroupAddForm extends AbstractForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.template.group.add';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.template.canManageTemplate');
	
	/**
	 * template group name
	 * @var	string
	 */
	public $templateGroupName = '';
	
	/**
	 * template group folder
	 * @var	integer
	 */
	public $templateGroupFolderName = '';
	
	/**
	 * parent template group id
	 * @var	integer
	 */
	public $parentTemplateGroupID = 0;
	
	/**
	 * available template groups
	 * @var	array
	 */
	public $availableTemplateGroups = array();
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['templateGroupName'])) $this->templateGroupName = StringUtil::trim($_POST['templateGroupName']);
		if (!empty($_POST['templateGroupFolderName'])) {
			$this->templateGroupFolderName = StringUtil::trim($_POST['templateGroupFolderName']);
			if ($this->templateGroupFolderName) $this->templateGroupFolderName = FileUtil::addTrailingSlash($this->templateGroupFolderName);
		}
		if (isset($_POST['parentTemplateGroupID'])) $this->parentTemplateGroupID = intval($_POST['parentTemplateGroupID']);
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->validateName();
		$this->validateFolderName();
		
		if ($this->parentTemplateGroupID && !isset($this->availableTemplateGroups[$this->parentTemplateGroupID])) {
			throw new UserInputException('parentTemplateGroupID', 'notValid');
		}
	}
	
	/**
	 * Validates the template group name.
	 */
	protected function validateName() {
		if (empty($this->templateGroupName)) {
			throw new UserInputException('templateGroupName');
		}
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_template_group
			WHERE	templateGroupName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->templateGroupName));
		$row = $statement->fetchArray();
		if ($row['count']) {
			throw new UserInputException('templateGroupName', 'notUnique');
		}
	}
	
	/**
	 * Validates the template group folder name.
	 */
	protected function validateFolderName() {
		if (empty($this->templateGroupFolderName)) {
			throw new UserInputException('templateGroupFolderName');
		}
		
		if (!preg_match('/^[a-z0-9_\- ]+\/$/i', $this->templateGroupFolderName)) {
			throw new UserInputException('templateGroupFolderName', 'notValid');
		}
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_template_group
			WHERE	templateGroupFolderName = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->templateGroupFolderName));
		$row = $statement->fetchArray();
		if ($row['count']) {
			throw new UserInputException('templateGroupFolderName', 'notUnique');
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new TemplateGroupAction(array(), 'create', array('data' => array_merge($this->additionalFields, array(
			'templateGroupName' => $this->templateGroupName,
			'templateGroupFolderName' => $this->templateGroupFolderName,
			'parentTemplateGroupID' => ($this->parentTemplateGroupID ?: null)
		))));
		$this->objectAction->executeAction();
		$this->saved();
		
		// reset values
		$this->templateGroupName = $this->templateGroupFolderName = '';
		$this->parentTemplateGroupID = 0;
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->availableTemplateGroups = TemplateGroup::getSelectList(array(), 1);
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'templateGroupName' => $this->templateGroupName,
			'templateGroupFolderName' => $this->templateGroupFolderName,
			'parentTemplateGroupID' => $this->parentTemplateGroupID,
			'availableTemplateGroups' => $this->availableTemplateGroups
		));
	}
}
