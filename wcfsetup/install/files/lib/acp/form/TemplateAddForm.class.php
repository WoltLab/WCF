<?php
namespace wcf\acp\form;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupAction;
use wcf\data\template\group\TemplateGroupList;
use wcf\data\template\Template;
use wcf\data\template\TemplateAction;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the form for adding new templates.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class TemplateAddForm extends AbstractForm {
	/**
	 * @see	wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.template.add';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.template.canManageTemplate');
	
	/**
	 * template name
	 * @var string
	 */
	public $tplName = '';
	
	/**
	 * template group id
	 * @var integer
	 */
	public $templateGroupID = 0;
	
	/**
	 * template source code
	 * @var string
	 */
	public $templateSource = '';
	
	/**
	 * available template groups
	 * @var array
	 */
	public $availableTemplateGroups = array();
	
	/**
	 * template's package id
	 * @var integer
	 */
	public $packageID = PACKAGE_ID;
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['tplName'])) $this->tplName = StringUtil::trim($_POST['tplName']);
		if (isset($_POST['templateSource'])) $this->templateSource = $_POST['templateSource'];
		if (isset($_POST['templateGroupID'])) $this->templateGroupID = intval($_POST['templateGroupID']);
		
		// get package id for this template
		$sql = "SELECT	packageID
			FROM	wcf".WCF_N."_template
			WHERE	templateName = ?
				AND templateGroupID IS NULL";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->tplName));
		$row = $statement->fetchArray();
		if ($row !== false) {
			$this->packageID = $row['packageID'];
		}
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->validateName();
		$this->validateGroup();
	}
	
	/**
	 * Validates the template name.
	 */
	protected function validateName() {
		if (empty($this->tplName)) {
			throw new UserInputException('tplName');
		}
	
		if (!preg_match('^/[a-z0-9_\-]+$/i', $this->tplName)) {
			throw new UserInputException('tplName', 'notValid');
		}
		
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_template
			WHERE	templateName = ?
				AND packageID = ?
				AND templateGroupID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->tplName,
			$this->packageID,
			$this->templateGroupID
		));
		$row = $statement->fetchArray();
		if ($row['count']) {
			throw new UserInputException('tplName', 'notUnique');
		}
	}
	
	/**
	 * Validates the selected template group.
	 */
	protected function validateGroup() {
		if (!$this->templateGroupID) {
			throw new UserInputException('templateGroupID');
		}
		
		$templateGroup = new TemplateGroup($this->templateGroupID);
		if (!$templateGroup->templateGroupID) {
			throw new UserInputException('templateGroupID');
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		$this->objectAction = new TemplateAction(array(), 'create', array('data' => array(
			'templateName' => $this->templateName,
			'packageID' => $this->packageID,
			'templateGroupID' => ($this->templateGroupID)
		), 'source' => $this->templateSource));
		$this->objectAction->executeAction();
		$this->saved();
		
		// reset values
		$this->templateName = $this->source = '';
		$this->templateGroupID = 0;
		
		// show success
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
	
		$templateGroupList = new TemplateGroupList();
		$templateGroupList->readObjects();
		$this->availableTemplateGroups = $templateGroupList->getObjects();
		
		if (!count($_POST)) {
			if (!empty($_REQUEST['copy'])) {
				$templateID = intval($_REQUEST['copy']);
				$template = new Template($templateID);
				$this->tplName = $template->templateName;
				$this->templateSource = $template->getSource();
			}
		}
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'tplName' => $this->tplName,
			'templateGroupID' => $this->templateGroupID,
			'templateSource' => $this->templateSource,
			'availableTemplateGroups' => $this->availableTemplateGroups
		));
	}
}
