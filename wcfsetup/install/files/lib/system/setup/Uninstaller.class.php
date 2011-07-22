<?php
namespace wcf\system\setup;

/**
 * Uninstaller deletes files and directories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.setup
 * @category 	Community Framework
 */
class Uninstaller {
	protected $targetDir, $files, $deleteEmptyTargetDir, $deleteEmptyDirectories;
	
	/**
	 * Creates a new Uninstaller object.
	 *
	 * @param	string		$targetDir
	 * @param	array		$files			delete the files
	 * @param	boolean		$deleteEmptyTargetDir	delete target dir if empty
	 * @param	boolean		$deleteEmptyDirectories	delete sub-directories if empty
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
	 * @return	boolean 			true if dir is empty
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
			$directories = array();
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
		// TODO: else throw SystemException ?
	}
	
}
