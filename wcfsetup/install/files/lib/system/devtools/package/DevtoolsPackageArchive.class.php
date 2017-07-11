<?php
namespace wcf\system\devtools\package;
use wcf\system\package\PackageArchive;

/**
 * @method		DevtoolsTar	getTar()
 */
class DevtoolsPackageArchive extends PackageArchive {
	protected $packageXmlPath = '';
	
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct($packageXmlPath) {
		$this->packageXmlPath = $packageXmlPath;
	}
	
	public function openArchive() {
		$this->tar = new DevtoolsTar(['package.xml' => $this->packageXmlPath]);
		
		$this->readPackageInfo();
	}
}
