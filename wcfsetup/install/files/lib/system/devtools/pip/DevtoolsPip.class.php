<?php
namespace wcf\system\devtools\pip;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\package\installation\plugin\PackageInstallationPlugin;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\JSON;

/**
 * Wrapper class for package installation plugins for use with the sync feature.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Pip
 * @since       3.1
 * 
 * @method	PackageInstallationPlugin	getDecoratedObject()
 * @mixin	PackageInstallationPlugin
 */
class DevtoolsPip extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PackageInstallationPlugin::class;
	
	/**
	 * Returns true if the PIP class can be found.
	 * 
	 * @return      boolean
	 */
	public function classExists() {
		return class_exists($this->getDecoratedObject()->className);
	}
	
	/**
	 * Returns true if the PIP is expected to be idempotent.
	 * 
	 * @return      boolean
	 */
	public function isIdempotent() {
		return is_subclass_of($this->getDecoratedObject()->className, IIdempotentPackageInstallationPlugin::class);
	}
	
	/**
	 * Returns the default filename of this PIP.
	 * 
	 * @return      string
	 */
	public function getDefaultFilename() {
		return call_user_func([$this->getDecoratedObject()->className, 'getDefaultFilename']);
	}
	
	public function getEffectiveDefaultFilename() {
		return './' . preg_replace('~\.tar$~', '/', $this->getDefaultFilename());
	}
	
	/**
	 * Returns true if the PIP exists, has a default filename and is idempotent.
	 * 
	 * @return      boolean
	 */
	public function isSupported() {
		return $this->classExists() && $this->getDefaultFilename() && $this->isIdempotent();
	}
	
	public function getSyncDependencies($toJson = true) {
		$dependencies = call_user_func([$this->getDecoratedObject()->className, 'getSyncDependencies']);
		
		return ($toJson) ? JSON::encode($dependencies) : $dependencies;
	}
	
	/**
	 * Returns the first validation error.
	 * 
	 * @return      string
	 */
	public function getFirstError() {
		if (!$this->classExists()) {
			return WCF::getLanguage()->getDynamicVariable('wcf.acp.devtools.pip.error.className', ['className' => $this->getDecoratedObject()->className]);
		}
		else if (!$this->isIdempotent()) {
			return WCF::getLanguage()->get('wcf.acp.devtools.pip.error.notIdempotent');
		}
		else if (!$this->getDefaultFilename()) {
			return WCF::getLanguage()->get('wcf.acp.devtools.pip.error.defaultFilename');
		}
		
		throw new \LogicException("Please call `isSupported()` to check for potential errors.");
	}
	
	/**
	 * Returns the list of valid targets for this pip.
	 * 
	 * @param       DevtoolsProject         $project
	 * @return      string[]
	 */
	public function getTargets(DevtoolsProject $project) {
		if (!$this->isSupported()) {
			return [];
		}
		
		$path = $project->path;
		$defaultFilename = $this->getDefaultFilename();
		$targets = [];
		
		// the core uses a significantly different file layout
		if ($project->isCore()) {
			switch ($this->getDecoratedObject()->pluginName) {
				case 'acpTemplate':
				case 'file':
				case 'template':
					// these pips are satisfied by definition
					return [$defaultFilename];
					
				case 'language':
					foreach (glob($path . 'wcfsetup/install/lang/*.xml') as $file) {
						$targets[] = basename($file);
					}
					
					// `glob()` returns files in an arbitrary order
					sort($targets, SORT_NATURAL);
					
					return $targets;
			}
			
			if (strpos($defaultFilename, '*') !== false) {
				foreach (glob($path . 'com.woltlab.wcf/' . $defaultFilename) as $file) {
					$targets[] = basename($file);
				}
				
				// `glob()` returns files in an arbitrary order
				sort($targets, SORT_NATURAL);
			}
			else {
				if (file_exists($path . 'com.woltlab.wcf/' . $defaultFilename)) {
					$targets[] = $defaultFilename;
				}
			}
		}
		else {
			if (preg_match('~^(?<filename>.*)\.tar$~', $defaultFilename, $match)) {
				if (is_dir($path . $match['filename'])) {
					$targets[] = $defaultFilename;
				}
				
				// check for application-specific pips too
				foreach (ApplicationHandler::getInstance()->getAbbreviations() as $abbreviation) {
					if (is_dir($path . $match['filename'] . '_' . $abbreviation)) {
						$targets[] = $match['filename'] . "_{$abbreviation}.tar";
					}
				}
			}
			else {
				if (strpos($defaultFilename, '*') !== false) {
					foreach (glob($path . $defaultFilename) as $file) {
						$targets[] = basename($file);
					}
					
					// `glob()` returns files in an arbitrary order
					sort($targets, SORT_NATURAL);
				}
				else {
					if (file_exists($path . $defaultFilename)) {
						$targets[] = $defaultFilename;
					}
				}
			}
		}
		
		return $targets;
	}
	
	/**
	 * Computes and prepares the instructions for the provided target file.
	 * 
	 * @param       DevtoolsProject         $project
	 * @param       string                  $target
	 * @return      string[]
	 */
	public function getInstructions(DevtoolsProject $project, $target) {
		$defaultFilename = $this->getDefaultFilename();
		$pluginName = $this->getDecoratedObject()->pluginName;
		$tar = $project->getPackageArchive()->getTar();
		$tar->reset();
		
		$instructions = [];
		
		if ($project->isCore()) {
			switch ($pluginName) {
				case 'acpTemplate':
				case 'file':
				case 'template':
					if ($pluginName === 'acpTemplate' || $pluginName === 'template') {
						$path = ($pluginName === 'acpTemplate') ? 'wcfsetup/install/files/acp/templates/' : 'com.woltlab.wcf/templates/';
						foreach (glob($project->path . $path . '*.tpl') as $template) {
							$tar->registerFile(basename($template), FileUtil::unifyDirSeparator($template));
						}
					}
					else {
						$path = 'wcfsetup/install/files/';
						
						$directory = new \RecursiveDirectoryIterator($project->path . $path);
						$filter = new \RecursiveCallbackFilterIterator($directory, function ($current) {
							/** @var \SplFileInfo $current */
							$filename = $current->getFilename();
							if ($filename[0] === '.') {
								// ignore dot files and files/directories starting with a dot
								return false;
							}
							else if ($filename === 'options.inc.php') {
								// ignores `options.inc.php` file which is only valid for installation
								return false;
							}
							else if ($filename === 'app.config.inc.php') {
								// ignores `app.config.inc.php` file which has a dummy contents for installation
								// and cannot be restored by WSC itself
								return false;
							}
							else if ($filename === 'templates') {
								// ignores both `templates` and `acp/templates`
								return false;
							}
							
							return true;
						});
						
						$iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
						foreach ($iterator as $value => $item) {
							/** @var \SplFileInfo $item */
							$itemPath = $item->getRealPath();
							if (is_dir($itemPath)) continue;
							
							$tar->registerFile(
								FileUtil::getRelativePath($project->path . $path, $item->getPath()) . $item->getFilename(),
								$itemPath
							);
						}
					}
					
					$instructions['value'] = $defaultFilename;
					
					break;
				
				case 'language':
					$filename = "wcfsetup/install/lang/{$target}";
					$tar->registerFile($filename, $project->path . $filename);
					
					$instructions['value'] = $filename;
					
					break;
					
				default:
					$filename = "com.woltlab.wcf/{$target}";
					$tar->registerFile($filename, $project->path . $filename);
					
					$instructions['value'] = $filename;
					
					break;
			}
		}
		else {
			switch ($pluginName) {
				case 'acpTemplate':
				case 'file':
				case 'template':
					if ($pluginName === 'acpTemplate' || $pluginName === 'template') {
						$path = ($pluginName === 'acpTemplate') ? 'acptemplates/' : 'templates/';
						foreach (glob($project->path . $path . '*.tpl') as $template) {
							$tar->registerFile(basename($template), FileUtil::unifyDirSeparator($template));
						}
					}
					else {
						$path = 'files/';
						if (preg_match('~^files_(?<application>.*)\.tar$~', $target, $match)) {
							$path = "files_{$match['application']}/";
							
							$instructions['attributes'] = ['application' => $match['application']];
						}
						
						$directory = new \RecursiveDirectoryIterator($project->path . $path);
						$filter = new \RecursiveCallbackFilterIterator($directory, function ($current) {
							/** @var \SplFileInfo $current */
							$filename = $current->getFilename();
							if ($filename[0] === '.') {
								// ignore dot files and files/directories starting with a dot
								return false;
							}
							
							return true;
						});
						
						$iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
						foreach ($iterator as $value => $item) {
							/** @var \SplFileInfo $item */
							$itemPath = $item->getRealPath();
							if (is_dir($itemPath)) continue;
							
							$tar->registerFile(
								FileUtil::getRelativePath($project->path . $path, $item->getPath()) . $item->getFilename(),
								$itemPath
							);
						}
					}
					
					$instructions['value'] = $defaultFilename;
					
					break;
				
				default:
					if (strpos($defaultFilename, '*') !== false) {
						$filename = str_replace('*', $target, $defaultFilename);
						$tar->registerFile($filename, $project->path . $filename);
					}
					else {
						$filename = $target;
						$tar->registerFile($filename, $project->path . $filename);
					}
					
					$instructions['value'] = $filename;
					
					break;
			}
		}
		
		return $instructions;
	}
}
