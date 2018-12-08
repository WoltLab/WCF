<?php
namespace wcf\data\devtools\project;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;

/**
 * Executes devtools project related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Devtools\Project
 * @since	3.1
 * 
 * @method	DevtoolsProjectEditor[]	getObjects()
 * @method	DevtoolsProjectEditor	getSingleObject()
 */
class DevtoolsProjectAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = DevtoolsProjectEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['delete'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.configuration.package.canInstallPackage'];
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		if (!ENABLE_DEVELOPER_TOOLS) {
			throw new IllegalLinkException();
		}
		
		parent::validateDelete();
	}
	
	/**
	 * Validates the 'quickSetup' action.
	 * 
	 * @throws	IllegalLinkException
	 */
	public function validateQuickSetup() {
		if (!ENABLE_DEVELOPER_TOOLS) {
			throw new IllegalLinkException();
		}
		
		WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);
		
		$this->readString('path');
	}
	
	/**
	 * Quickly setups multiple projects by scanning a directory.
	 * 
	 * @return	array
	 */
	public function quickSetup() {
		if (!is_dir($this->parameters['path'])) {
			return [
				'errorMessage' => WCF::getLanguage()->get('wcf.acp.devtools.project.path.error.notFound'),
				'errorType' => 'notFound'
			];
		}
		
		$path = FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator($this->parameters['path']));
		
		// read all project names and paths
		$projectList = new DevtoolsProjectList();
		$projectList->readObjects();
		
		$projectNames = $projectPaths = [];
		foreach ($projectList as $project) {
			$projectNames[] = $project->name;
			$projectPaths[] = $project->path;
		}
		
		$projectCount = 0;
		
		$directoryUtil = DirectoryUtil::getInstance($path, false);
		$directoryUtil->executeCallback(function($directory) use ($path, $projectNames, $projectPaths, &$projectCount) {
			$projectPath = $path . $directory . '/';
			
			// validate path
			if (DevtoolsProject::validatePath($projectPath) !== '') {
				return;
			}
			
			// only consider paths that are not already used by a different project
			if (in_array($projectPath, $projectPaths)) {
				return;
			}
			
			// make sure that project name is unique
			$name = $directory;
			
			$iteration = 1;
			while (in_array($name, $projectNames)) {
				$name = $directory . ' (' . ($iteration++) . ')';
			}
			
			(new DevtoolsProjectAction([], 'create', ['data' => [
				'name' => $name,
				'path' => $projectPath
			]]))->executeAction();
			
			$projectCount++;
		});
		
		if (!$projectCount) {
			return [
				'errorMessage' => WCF::getLanguage()->get('wcf.acp.devtools.project.quickSetup.path.error.noPackages'),
				'errorType' => 'noPackages'
			];
		}
		
		return [
			'successMessage' => WCF::getLanguage()->getDynamicVariable('wcf.acp.devtools.project.quickSetup.success', [
				'count' => $projectCount
			])
		];
	}
}
