<?php
namespace wcf\acp\form;
use wcf\data\application\group\ApplicationGroup;
use wcf\data\application\group\ApplicationGroupAction;
use wcf\data\application\ViewableApplicationList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Shows the application group edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class ApplicationGroupEditForm extends ApplicationGroupAddForm {
	/**
	 * application group object
	 * @var	wcf\data\application\group\ApplicationGroup
	 */
	public $applicationGroup = null;
	
	/**
	 * groupd id
	 * @var	integer
	 */
	public $groupID = 0;
	
	/**
	 * @see	wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->groupID = intval($_REQUEST['id']);
		$this->applicationGroup = new ApplicationGroup($this->groupID);
		if (!$this->applicationGroup->groupID) {
			throw new IllegalLinkException();
		}
		
		parent::readParameters();
	}
	
	/**
	 * Reads the list of available applications.
	 */
	protected function readAvailableApplications() {
		$applicationList = new ViewableApplicationList();
		$applicationList->getConditionBuilder()->add("(application.groupID = ? OR application.groupID IS NULL)", array($this->applicationGroup->groupID));
		$applicationList->sqlLimit = 0;
		$applicationList->readObjects();
		
		$this->availableApplications = $applicationList->getObjects();
		foreach ($this->availableApplications as $application) {
			if ($application->groupID == $this->applicationGroup->groupID) {
				$this->applications[] = $application->packageID;
			}
		}
	}
	
	/**
	 * @see	wcf\acp\form\ApplicationGroupAddForm::validateGroupName()
	 */
	protected function validateGroupName() {
		if (empty($this->groupName)) {
			throw new UserInputException('groupName');
		}
		else {
			// check for duplicates
			$sql = "SELECT	COUNT(*) AS count
				FROM	wcf".WCF_N."_application_group
				WHERE	groupName = ?
					AND groupID <> ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->groupName,
				$this->applicationGroup->groupID
			));
			$row = $statement->fetchArray();
			if ($row['count']) {
				throw new UserInputException('groupName', 'notUnique');
			}
		}
	}
	
	/**
	 * @see	wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->groupName = $this->applicationGroup->groupName;
			
			foreach ($this->availableApplications as $application) {
				if ($application->isPrimary) {
					$this->primaryApplication = $application->packageID;
					break;
				}
			}
		}
	}
	
	/**
	 * @see	wcf\form\IForm::save()
	 */
	public function save() {
		ACPForm::save();
		
		// save group
		$this->objectAction = new ApplicationGroupAction(array($this->applicationGroup), 'update', array(
			'applications' => $this->applications,
			'data' => array(
				'groupName' => $this->groupName
			),
			'primaryApplication' => $this->primaryApplication
		));
		$this->objectAction->executeAction();
		$this->saved();
		
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
			'action' => 'edit',
			'applicationGroup' => $this->applicationGroup,
			'groupID' => $this->groupID
		));
	}
}
