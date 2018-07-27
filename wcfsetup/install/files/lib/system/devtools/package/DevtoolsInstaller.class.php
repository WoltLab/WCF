<?php
namespace wcf\system\devtools\package;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\system\setup\Installer;

/**
 * Specialized implementation to emulate a regular package installation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Package
 * @since       3.1
 */
class DevtoolsInstaller extends Installer {
	/**
	 * @var DevtoolsProject
	 */
	protected $project;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(DevtoolsProject $project, $targetDir, $source, $fileHandler = null, $folder = '') {
		$this->project = $project;
		
		parent::__construct($targetDir, $source, $fileHandler, $folder);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTar($source) {
		return $this->project->getPackageArchive()->getTar();
	}
}
