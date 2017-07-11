<?php
namespace wcf\system\devtools\pip;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\system\devtools\package\DevtoolsPackageArchive;
use wcf\system\package\PackageInstallationDispatcher;

class DevtoolsPackageInstallationDispatcher extends PackageInstallationDispatcher {
	/**
	 * @var DevtoolsPackageArchive
	 */
	protected $project;
	
	public function __construct(DevtoolsProject $project) {
		parent::__construct(new DevtoolsPackageInstallationQueue($project));
		
		$this->project = $project;
	}
	
	public function getArchive() {
		return $this->project->getPackageArchive();
	}
	
	public function getPackageID() {
		return $this->project->getPackage()->packageID;
	}
	
	public function getPackageName() {
		return $this->project->getPackage()->getName();
	}
}
