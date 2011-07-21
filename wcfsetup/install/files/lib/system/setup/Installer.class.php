<?php
namespace wcf\system\setup;
use wcf\system\exception\SystemException;
use wcf\system\io\Tar;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Installer extracts folders and files from a tar archive.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.setup
 * @category 	Community Framework
 */
class Installer {
	protected $targetDir, $source, $folder, $fileHandler;
	
	/**
	 * Creates a new Installer object.
	 *
	 * @param	string		$targetDir	extract the files in this dir
	 * @param	string		$source		name of the source tar archive
	 * @param 	FileHandler	$fileHandler
	 * @param	string		$folder		extract only the files from this subfolder
	 */
	public function __construct($targetDir, $source, $fileHandler = null, $folder = '') {
		$this->targetDir = FileUtil::addTrailingSlash($targetDir);
		$this->source = $source;
		$this->folder = $folder;
		$this->fileHandler = $fileHandler;
		$this->install();
	}
	
	/**
	 * Creates the target directory if necessary.
	 */
	protected function createTargetDir() {
		if (!@is_dir($this->targetDir)) {
			if (!FileUtil::makePath($this->targetDir, (FileUtil::isApacheModule() ? 0777 : 0755))) {
				throw new SystemException("Could not create dir '".$this->targetDir."'", 11011);
			}
		}
		if (FileUtil::isApacheModule() || !is_writeable($this->targetDir)) {
			$this->makeWriteable($this->targetDir);
		}
	}
	
	/**
	 * Creates a directory in the target directory.
	 *
	 * @param	string		$dir
	 */
	protected function createDir($dir) {
		if (!@is_dir($this->targetDir.$dir)) {
			$oldumask = umask(0);
			if (!@mkdir($this->targetDir.$dir, 0755, true)) {
				throw new SystemException("Could not create dir '".$this->targetDir.$dir."'", 11011);
			}
			umask($oldumask);
		}
		if (FileUtil::isApacheModule() || !is_writeable($this->targetDir.$dir)) {
			$this->makeWriteable($this->targetDir.$dir);
		}
	}
	
	/**
	 * Touches a file in the target directory.
	 *
	 * @param	string		$file
	 */
	public function touchFile($file) {
		@touch($this->targetDir.$file);
		$this->makeWriteable($this->targetDir.$file);
	}
	
	/**
	 * Creates a file in the target directory.
	 *
	 * @param	string		$file
	 * @param	integer		$index
	 * @param	Tar		$tar
	 */
	protected function createFile($file, $index, Tar $tar) {
		$tar->extract($index, $this->targetDir.$file);
		if (FileUtil::isApacheModule() || !is_writeable($this->targetDir.$file)) {
			$this->makeWriteable($this->targetDir.$file);
		}
	}
	
	/**
	 * Starts the extracting of the files.
	 */
	protected function install() {
		$this->createTargetDir();
		
		// open source archive
		$tar = new Tar($this->source);
		
		// distinct directories and files
		$directories = array();
		$files = array();
		foreach ($tar->getContentList() as $index => $file) {
			if (empty($this->folder) || StringUtil::indexOf($file['filename'], $this->folder) === 0) {
				if (!empty($this->folder)) {
					$file['filename'] = StringUtil::replace($this->folder, '', $file['filename']);
				}
				
				// remove leading slash
				$file['filename'] = FileUtil::removeLeadingSlash($file['filename']);
				if ($file['type'] == 'folder') {
					// remove trailing slash
					$directories[] = FileUtil::removeTrailingSlash($file['filename']);
				}
				else {
					$files[$index] = $file['filename'];
				}
			}
		}
		
		$this->checkFiles($files);

		// now create the directories
		$errors = array();
		foreach ($directories as $dir) {
			try {
				$this->createDir($dir);
			}
			catch (SystemException $e) {
				$errors[] = array('file' => $dir, 'code' => $e->getCode(), 'message' => $e->getMessage());
			}
		}

		// now untar all files
		foreach ($files as $index => $file) {
			try {
				$this->createFile($file, $index, $tar);
			}
			catch (SystemException $e) {
				$errors[] = array('file' => $file, 'code' => $e->getCode(), 'message' => $e->getMessage());
			}
		}
		if (count($errors) > 0) {
			throw new SystemException('error(s) during the installation of the files.', 11111, $errors);
		}
		
		$this->logFiles($files);

		// close tar
		$tar->close();
	}
	
	/**
	 * Checkes whether the given files overwriting locked existing files.
	 *
	 * @param	array		$files		list of files
	 */
	protected function checkFiles(&$files) {
		if ($this->fileHandler != null && $this->fileHandler instanceof IFileHandler) {
			$this->fileHandler->checkFiles($files);
		}
	}
	
	/**
	 * Logs the extracted files.
	 *
	 * @param	array		$files		list of files
	 */
	protected function logFiles(&$files) {
		if ($this->fileHandler != null && $this->fileHandler instanceof IFileHandler) {
			$this->fileHandler->logFiles($files);
		}
	}
	
	/**
	 * Makes a file or directory writeable.
	 *
	 * @param	string		$target
	 */
	protected function makeWriteable($target) {
		if (!preg_match('/^WIN/i', PHP_OS)) {
			if (!@chmod($target, 0777)) {
				//throw new SystemException("Could not chmod file '".$target."'", 11005);
			}
		}
	}
}
