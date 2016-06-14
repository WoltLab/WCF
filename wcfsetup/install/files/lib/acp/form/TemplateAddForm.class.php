<?php
namespace wcf\acp\form;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\Template;
use wcf\data\template\TemplateAction;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the form for adding new templates.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 */
class TemplateAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.template.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.template.canManageTemplate'];
	
	/**
	 * template name
	 * @var	string
	 */
	public $tplName = '';
	
	/**
	 * template group id
	 * @var	integer
	 */
	public $templateGroupID = 0;
	
	/**
	 * template source code
	 * @var	string
	 */
	public $templateSource = '';
	
	/**
	 * available template groups
	 * @var	array
	 */
	public $availableTemplateGroups = [];
	
	/**
	 * template's package id
	 * @var	integer
	 */
	public $packageID = 1;
	
	/**
	 * id of copied template
	 * @var	integer
	 */
	public $copy = 0;
	
	/**
	 * copied template object
	 * @var	\wcf\data\template\Template
	 */
	public $copiedTemplate = null;
	
	/**
	 * application the template belongs to
	 * @var	string
	 */
	public $application = '';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!empty($_REQUEST['copy'])) {
			$this->copy = intval($_REQUEST['copy']);
			$this->copiedTemplate = new Template($this->copy);
			if (!$this->copiedTemplate->templateID) {
				throw new IllegalLinkException();
			}
			
			$this->application = $this->copiedTemplate->application;
			$this->packageID = $this->copiedTemplate->packageID;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['tplName'])) $this->tplName = StringUtil::trim($_POST['tplName']);
		if (isset($_POST['templateSource'])) $this->templateSource = StringUtil::unifyNewlines($_POST['templateSource']);
		if (isset($_POST['templateGroupID'])) $this->templateGroupID = intval($_POST['templateGroupID']);
		
		// get package id for this template
		if (!$this->packageID) {
			$sql = "SELECT	packageID
				FROM	wcf".WCF_N."_template
				WHERE	templateName = ?
					AND templateGroupID IS NULL";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$this->tplName]);
			$row = $statement->fetchArray();
			if ($row !== false) {
				$this->packageID = $row['packageID'];
			}
		}
	}
	
	/**
	 * @inheritDoc
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
		
		if (!preg_match('/^[a-z0-9_\-]+$/i', $this->tplName)) {
			throw new UserInputException('tplName', 'notValid');
		}
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('templateName = ?', [$this->tplName]);
		$conditionBuilder->add('templateGroupID = ?', [$this->templateGroupID]);
		
		if ($this->copiedTemplate !== null) {
			$conditionBuilder->add('(packageID = ? OR application = ?)', [$this->packageID, $this->copiedTemplate->application]);
		}
		else {
			$conditionBuilder->add('packageID = ?', [$this->packageID]);
		}
		
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_template
			".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		if ($statement->fetchSingleColumn()) {
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
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		if (empty($this->application)) {
			$this->application = Package::getAbbreviation(PackageCache::getInstance()->getPackage($this->packageID)->package);
		}
		
		$this->objectAction = new TemplateAction([], 'create', ['data' => array_merge($this->additionalFields, [
			'application' => $this->application,
			'templateName' => $this->tplName,
			'packageID' => $this->packageID,
			'templateGroupID' => $this->templateGroupID
		]), 'source' => $this->templateSource]);
		$this->objectAction->executeAction();
		$this->saved();
		
		// reset values
		$this->tplName = $this->templateSource = '';
		$this->templateGroupID = 0;
		
		// show success
		WCF::getTPL()->assign([
			'success' => true
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->availableTemplateGroups = TemplateGroup::getSelectList();
		
		if (!count($_POST) && $this->copiedTemplate !== null) {
			$this->tplName = $this->copiedTemplate->templateName;
			$this->templateSource = $this->copiedTemplate->getSource();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'add',
			'tplName' => $this->tplName,
			'templateGroupID' => $this->templateGroupID,
			'templateSource' => $this->templateSource,
			'availableTemplateGroups' => $this->availableTemplateGroups,
			'copy' => $this->copy
		]);
	}
}
