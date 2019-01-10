<?php
namespace wcf\system\devtools\pip;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\system\devtools\package\DevtoolsInstaller;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\package\PackageInstallationNodeBuilder;

/**
 * Specialized implementation to emulate a regular package installation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Pip
 * @since       3.1
 */
class DevtoolsPackageInstallationDispatcher extends PackageInstallationDispatcher {
	/**
	 * @var	DevtoolsProject
	 */
	protected $project;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(DevtoolsProject $project, PackageInstallationQueue $queue = null) {
		$this->queue = $queue;
		if ($this->queue === null) {
			$this->queue = new DevtoolsPackageInstallationQueue($project);
		}
		
		$this->nodeBuilder = new class($this) extends PackageInstallationNodeBuilder {
			protected function buildOptionalNodes() {
				// does nothing; optional packages are not supported
			}
		};
		
		$this->action = $this->queue->action;
		
		$this->project = $project;
	}
	
	/**
	 * @inheritDoc
	 * @since	5.2
	 */
	protected function createPackage(array $packageData) {
		$package = parent::createPackage($packageData);
		
		$this->project->setPackage($package);
		
		return $package;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getArchive() {
		return $this->project->getPackageArchive();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPackage() {
		return $this->project->getPackage();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPackageID() {
		return $this->project->getPackage()->packageID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPackageName() {
		return $this->project->getPackage()->getName();
	}
	
	/**
	 * @inheritDoc
	 */
	public function extractFiles($targetDir, $sourceArchive, $fileHandler = null) {
		/** @noinspection PhpParamsInspection */
		return new DevtoolsInstaller($this->project, $targetDir, $sourceArchive, $fileHandler);
	}
	
	/**
	 * Returns the project the installation dispatcher is created for.
	 * 
	 * @return	DevtoolsProject
	 * @since	5.2
	 */
	public function getProject() {
		return $this->project;
	}
}
