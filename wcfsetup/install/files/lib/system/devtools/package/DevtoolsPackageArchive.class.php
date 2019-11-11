<?php
namespace wcf\system\devtools\package;
use wcf\system\package\plugin\ACPTemplatePackageInstallationPlugin;
use wcf\system\package\plugin\FilePackageInstallationPlugin;
use wcf\system\package\plugin\TemplatePackageInstallationPlugin;
use wcf\system\package\PackageArchive;
use wcf\system\Regex;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;

/**
 * Specialized implementation to emulate a regular package installation.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
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
		$projectDir = FileUtil::addTrailingSlash(realpath(dirname($this->packageXmlPath)));
		
		$readFiles = DirectoryUtil::getInstance($projectDir)->getFiles(
			SORT_ASC,
			// ignore folders whose contents are delivered as archives by default
			// and ignore dotfiles and dotdirectories
			Regex::compile('^' . preg_quote($projectDir) . '(acptemplates|files|templates|\.)'), 
			true
		);
		
		$files = [];
		foreach ($readFiles as $file) {
			if (is_file($file)) {
				$files[str_replace($projectDir, '', $file)] = $file;
			}
		}
		
		$this->tar = new DevtoolsTar($files);
		
		$this->readPackageInfo();
		foreach ($this->getInstallInstructions() as $instruction) {
			$archive = null;
			switch ($instruction['pip']) {
				case 'acpTemplate':
					$archive = $instruction['value'] ?: ACPTemplatePackageInstallationPlugin::getDefaultFilename();
					break;
				
				case 'file':
					$archive = $instruction['value'] ?: FilePackageInstallationPlugin::getDefaultFilename();
					break;
				
				case 'template':
					$archive = $instruction['value'] ?: TemplatePackageInstallationPlugin::getDefaultFilename();
					break;
			}
			
			if ($archive !== null) {
				$this->tar->registerFile($archive, $projectDir . $archive);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function extractTar($filename, $tempPrefix = 'package_') {
		return $filename;
	}
}
