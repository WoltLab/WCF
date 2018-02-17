<?php
namespace wcf\system\devtools\package;
use wcf\system\package\PackageArchive;

/**
 * Specialized implementation to emulate a regular package installation.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Package
 * @since       3.1
 * 
 * @method	DevtoolsTar	getTar()
 */
class DevtoolsPackageArchive extends PackageArchive {
	protected $packageXmlPath = '';
	
	/** @noinspection PhpMissingParentConstructorInspection @inheritDoc */
	public function __construct($packageXmlPath) {
		$this->packageXmlPath = $packageXmlPath;
	}
	
	
	/**
	 * @inheritDoc
	 */
	public function openArchive() {
		$this->tar = new DevtoolsTar(['package.xml' => $this->packageXmlPath]);
		
		$this->readPackageInfo();
	}
	
	/**
	 * @inheritDoc
	 */
	public function extractTar($filename, $tempPrefix = 'package_') {
		return $tempPrefix . $filename . '_dummy';
	}
}
