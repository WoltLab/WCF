<?php
namespace wcf\acp\form;
use wcf\data\application\ViewableApplicationList;
use wcf\data\application\group\ApplicationGroupAction;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the application group add form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class ApplicationGroupAddForm extends ACPForm {
	/**
	 * @see	wcf\acp\form\ACPForm::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.application';
	
	/**
	 * list of application package ids
	 * @var	array<integer>
	 */
	public $applications = array();
	
	/**
	 * list of available applications
	 * @var	array<wcf\data\application\ViewableApplication>
	 */
	public $availableApplications = array();
	
	/**
	 * group name
	 * @var	string
	 */
	public $groupName = '';
	
	/**
	 * @see	wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canManageApplication');
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->readAvailableApplications();
	}
	
	/**
	 * Reads the list of available applications.
	 */
	protected function readAvailableApplications() {
		$applicationList = new ViewableApplicationList();
		$applicationList->getConditionBuilder()->add("application.groupID IS NULL");
		$applicationList->sqlLimit = 0;
		$applicationList->readObjects();
		
		$this->availableApplications = $applicationList->getObjects();
	}
	
	/**
	 * @see	wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['applications']) && is_array($_POST['applications'])) $this->applications = ArrayUtil::toIntegerArray($_POST['applications']);
		if (isset($_POST['groupName'])) $this->groupName = StringUtil::trim($_POST['groupName']);
	}
	
	/**
	 * @see	wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate group name
		$this->validateGroupName();
		
		// validate application package ids
		if (empty($this->applications)) {
			throw new UserInputException('applications');
		}
		else {
			$this->applications = array_unique($this->applications);
			
			// require at least two applications
			if (count($this->applications) == 1) {
				throw new UserInputException('applications', 'single');
			}
			
			$packages = array();
			foreach ($this->applications as $packageID) {
				// unknown package id
				if (!isset($this->availableApplications[$packageID])) {
					throw new UserInputException('applications', 'notValid');
				}
				
				$application = $this->availableApplications[$packageID];
				
				// cannot group two or more applications of the same type
				if (in_array($application->getPackage()->package, $packages)) {
					throw new UserInputException('applications', 'duplicate');
				}
				
				$packages[] = $application->getPackage()->package;
			}
		}
	}
	
	/**
	 * Validates group name.
	 */
	protected function validateGroupName() {
		if (empty($this->groupName)) {
			throw new UserInputException('groupName');
		}
		else {
			// check for duplicates
			$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_application_group
				WHERE	groupName = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($this->groupName));
			$row = $statement->fetchArray();
			if ($row['count']) {
				throw new UserInputException('groupName', 'notUnique');
			}
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// save group
		$this->objectAction = new ApplicationGroupAction(array(), 'create', array(
			'applications' => $this->applications,
			'data' => array(
				'groupName' => $this->groupName
			)
		));
		$this->objectAction->executeAction();
		$this->saved();
		
		// reset values
		$this->applications = array();
		$this->groupName = '';
		
		// reload available applications
		$this->readAvailableApplications();
		
		// show success.
		WCF::getTPL()->assign(array(
			'success' => true
		));
	}
	
	/**
	 * @see	wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'applications' => $this->applications,
			'availableApplications' => $this->availableApplications,
			'groupName' => $this->groupName
		));
	}
}
