<?php
namespace wcf\system\setup;

/**
 * Deletes files and directories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Setup
 */
class Uninstaller {
	/**
	 * directory which contains the files to delete
	 * @var	string
	 */
	protected $targetDir;
	
	/**
	 * files to delete
	 * @var	string[]
	 */
	protected $files;
	
	/**
	 * indicates if target directory will be deleted if empty
	 * @var	boolean
	 */
	protected $deleteEmptyTargetDir;
	
	/**
	 * indicates if sub directory will be deleted if empty
	 * @var	boolean
	 */
	protected $deleteEmptyDirectories;
	
	/**
	 * Creates a new Uninstaller object.
	 * 
	 * @param	string		$targetDir
	 * @param	array		$files
	 * @param	boolean		$deleteEmptyTargetDir
	 * @param	boolean		$deleteEmptyDirectories
	 */
	public function __construct($targetDir, $files, $deleteEmptyTargetDir, $deleteEmptyDirectories) {
		$this->targetDir = $targetDir;
		$this->files = $files;
		$this->deleteEmptyTargetDir = $deleteEmptyTargetDir;
		$this->deleteEmptyDirectories = $deleteEmptyDirectories;
		$this->uninstall();
	}
	
	/**
	 * Checks if the target directory is a valid directory.
	 */
	protected function checkTargetDir() {
		return is_dir($this->targetDir);
	}
	
	/**
	 * Returns true if a directory is emtpy.
	 * 
	 * @param	string		$dir
	 * @return	boolean
	 */
	protected function isEmpty($dir) {
		if (is_dir($dir)) {
			// subtract default directories . and ..
			return count(scandir($dir)) - 2 == 0;
		}
		return false;
	}
	
	/**
	 * Deletes a file.
	 * 
	 * @param	string		$file
	 */
	protected function deleteFile($file) {
		@unlink($file);
	}
	
	/**
	 * Deletes a directory.
	 * 
	 * @param	string		$dir
	 */
	protected function deleteDir($dir) {
		@rmdir($dir);
	}
	
	/**
	 * Starts the deletion of the files.
	 */
	protected function uninstall() {
		if ($this->checkTargetDir()) {
			// delete all files
			$directories = [];
			foreach ($this->files as $file) {
				$this->deleteFile($this->targetDir.$file);
				
				// store directory if this file is not direct in the targetdirectory
				$dirname = dirname($file);
				if ($dirname != '.') {
					// split the directory
					$path = explode("/", $dirname);
					
					// store the directory in a key which is the representation
					// for the depth of this directory.
					// use dirname as key to store each dir only once
					$directories[count($path)][$dirname] = $dirname;
					
					// store the parent directories
					while (array_pop($path)) {
						$dirname = implode("/", $path);
						if (!empty($dirname)) {
							$directories[count($path)][$dirname] = $dirname;
						}
					}
				}
			}
			
			// delete direcotries
			if ($this->deleteEmptyDirectories) {
				// the deepest diretories first
				krsort($directories, SORT_NUMERIC);
				foreach ($directories as $depth) {
					foreach ($depth as $dir) {
						if ($this->isEmpty($this->targetDir.$dir)) {
							$this->deleteDir($this->targetDir.$dir);
						}
					}
				}
			}
			
			// delete target directory
			if ($this->deleteEmptyTargetDir && $this->isEmpty($this->targetDir)) {
				$this->deleteDir($this->targetDir);
			}
		}
	}
}
