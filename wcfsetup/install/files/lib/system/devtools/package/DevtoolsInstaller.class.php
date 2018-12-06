<?php
namespace wcf\system\devtools\package;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\system\package\plugin\ACPTemplatePackageInstallationPlugin;
use wcf\system\package\plugin\FilePackageInstallationPlugin;
use wcf\system\package\plugin\TemplatePackageInstallationPlugin;
use wcf\system\setup\Installer;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;

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
		$directory = null;
		
		$instructions = $this->project->getPackageArchive()->getInstallInstructions();
		
		// WoltLab Suite Core does not install its acp templates and files via PIP
		if ($this->project->isCore()) {
			$instructions[] = [
				'attributes' => ['type' => 'acpTemplate'],
				'pip' => 'acpTemplate',
				'value' => ''
			];
			
			$instructions[] = [
				'attributes' => ['type' => 'file'],
				'pip' => 'file',
				'value' => ''
			];
		}
		
		foreach ($instructions as $instruction) {
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
				if ($this->project->isCore()) {
					switch ($instruction['pip']) {
						case 'acpTemplate':
							$directory = $this->project->path . 'wcfsetup/install/files/acp/templates/';
							break;
						
						case 'file':
							$directory = $this->project->path . 'wcfsetup/install/files/';
							break;
						
						case 'template':
							$directory = $this->project->path . 'com.woltlab.wcf/templates/';
							break;
					}
				}
				else {
					$directory = FileUtil::addTrailingSlash($this->project->path . pathinfo($archive, PATHINFO_FILENAME));
				}
				
				if ($source == $archive && is_dir($directory)) {
					$files = $this->project->getPackageArchive()->getTar()->getFiles();
					
					foreach ($this->project->getPips() as $pip) {
						if ($pip->pluginName === $instruction['pip']) {
							$pip->getInstructions($this->project, $source);
							
							$tar = new DevtoolsTar($this->project->getPackageArchive()->getTar()->getFiles());
							
							$this->project->getPackageArchive()->getTar()->setFiles($files);
							
							return $tar;
						}
					}
				}
			}
			
		}
		
		throw new \InvalidArgumentException("Unknown file '{$source}'");
	}
}
