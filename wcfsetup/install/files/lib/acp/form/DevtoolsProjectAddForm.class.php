<?php
namespace wcf\acp\form;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\devtools\project\DevtoolsProjectAction;
use wcf\form\AbstractForm;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Shows the devtools project add form.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since       3.1
 */
class DevtoolsProjectAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.devtools.project.add';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['ENABLE_DEVELOPER_TOOLS'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canInstallPackage'];
	
	/**
	 * cronjob class name
	 * @var	string
	 */
	public $name = '';
	
	/**
	 * cronjob path
	 * @var	string
	 */
	public $path = '';
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['name'])) $this->name = StringUtil::trim($_POST['name']);
		if (isset($_POST['path'])) $this->path = StringUtil::trim($_POST['path']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// validate name
		if (empty($this->name)) {
			throw new UserInputException('name');
		}
		else {
			$this->validateUniqueName();
		}
		
		// validate path
		if (empty($this->path)) {
			throw new UserInputException('path');
		}
		else {
			$path = FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator($this->path));
			$errorType = DevtoolsProject::validatePath($path);
			if ($errorType !== '') {
				throw new UserInputException('path', $errorType);
			}
			
			$this->validateUniquePath();
			
			$this->path = $path;
		}
	}
	
	/**
	 * Checks that the project name is not used by another project.
	 */
	protected function validateUniqueName() {
		$sql = "SELECT  COUNT(*)
			FROM    wcf".WCF_N."_devtools_project
			WHERE   name = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->name]);
		
		if ($statement->fetchSingleColumn()) {
			throw new UserInputException('name', 'notUnique');
		}
	}
	
	/**
	 * Checks that the project path is not used by another project.
	 */
	protected function validateUniquePath() {
		$sql = "SELECT  COUNT(*)
			FROM    wcf".WCF_N."_devtools_project
			WHERE   path = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->path]);
		
		if ($statement->fetchSingleColumn()) {
			throw new UserInputException('path', 'notUnique');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save cronjob
		$data = array_merge($this->additionalFields, [
			'name' => $this->name,
			'path' => $this->path
		]);
		
		$this->objectAction = new DevtoolsProjectAction([], 'create', ['data' => $data]);
		$this->objectAction->executeAction();
		
		$this->saved();
		
		// reset values
		$this->name = $this->path = '';
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'name' => $this->name,
			'path' => $this->path,
			'action' => 'add'
		]);
	}
}
