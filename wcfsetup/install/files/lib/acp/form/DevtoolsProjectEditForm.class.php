<?php
namespace wcf\acp\form;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\devtools\project\DevtoolsProjectAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Shows the devtools project edit form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since       3.1
 */
class DevtoolsProjectEditForm extends DevtoolsProjectAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.devtools.project.list';
	
	/**
	 * project id
	 * @var	integer
	 */
	public $objectID = 0;
	
	/**
	 * devtools project
	 * @var	DevtoolsProject
	 */
	public $object;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->objectID = intval($_REQUEST['id']);
		$this->object = new DevtoolsProject($this->objectID);
		if (!$this->object->projectID) {
			throw new IllegalLinkException();
		}
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	protected function validateUniqueName() {
		$sql = "SELECT  COUNT(*)
			FROM    wcf".WCF_N."_devtools_project
			WHERE   name = ?
				AND projectID <> ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->name, $this->objectID]);
		
		if ($statement->fetchSingleColumn()) {
			throw new UserInputException('name', 'notUnique');
		}
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	protected function validateUniquePath() {
		$sql = "SELECT  COUNT(*)
			FROM    wcf".WCF_N."_devtools_project
			WHERE   path = ?
				AND projectID <> ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->path, $this->objectID]);
		
		if ($statement->fetchSingleColumn()) {
			throw new UserInputException('path', 'notUnique');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->name = $this->object->name;
			$this->path = $this->object->path;
		}
	}
	
	/** @noinspection PhpMissingParentCallCommonInspection */
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// update cronjob
		$data = array_merge($this->additionalFields, [
			'name' => $this->name,
			'path' => $this->path
		]);
		
		$this->objectAction = new DevtoolsProjectAction([$this->objectID], 'update', ['data' => $data]);
		$this->objectAction->executeAction();
		
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'objectID' => $this->objectID,
			'action' => 'edit'
		]);
	}
}
