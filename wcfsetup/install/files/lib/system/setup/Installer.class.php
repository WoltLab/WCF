<?php
namespace wcf\system\setup;
use wcf\system\exception\SystemException;
use wcf\system\io\Tar;
use wcf\util\FileUtil;

/**
 * Extracts files and directories from a tar archive.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Setup
 */
class Installer {
	/**
	 * directory the files are installed into
	 * @var	string
	 */
	protected $targetDir;
	
	/**
	 * name of the source tar archive
	 * @var	string
	 */
	protected $source;
	
	/**
	 * folder within source that limits the installed files to those within
	 * this folder
	 * @var	string
	 */
	protected $folder;
	
	/**
	 * file handler of the installed files
	 * @var	\wcf\system\setup\IFileHandler
	 */
	protected $fileHandler;
	
	/**
	 * Creates a new Installer object.
	 * 
	 * @param	string				$targetDir
	 * @param	string				$source
	 * @param	\wcf\system\setup\IFileHandler	$fileHandler
	 * @param	string				$folder
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
			if (!FileUtil::makePath($this->targetDir)) {
				throw new SystemException("Could not create dir '".$this->targetDir."'");
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
	 * @throws	SystemException
	 */
	protected function createDir($dir) {
		if (!@is_dir($this->targetDir.$dir)) {
			$oldumask = umask(0);
			if (!@mkdir($this->targetDir.$dir, 0755, true)) {
				throw new SystemException("Could not create dir '".$this->targetDir.$dir."'");
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
		$directories = [];
		$files = [];
		foreach ($tar->getContentList() as $index => $file) {
			if (empty($this->folder) || mb_strpos($file['filename'], $this->folder) === 0) {
				if (!empty($this->folder)) {
					$file['filename'] = str_replace($this->folder, '', $file['filename']);
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
		$errors = [];
		foreach ($directories as $dir) {
			try {
				$this->createDir($dir);
			}
			catch (SystemException $e) {
				$errors[] = $e->getMessage();
			}
		}
		
		// now untar all files
		foreach ($files as $index => $file) {
			try {
				$this->createFile($file, $index, $tar);
			}
			catch (SystemException $e) {
				$errors[] = $e->getMessage();
			}
		}
		if (!empty($errors)) {
			throw new SystemException('error(s) during the installation of the files.', 0, implode("<br>", $errors));
		}
		
		$this->logFiles($files);
		
		// close tar
		$tar->close();
	}
	
	/**
	 * Checkes whether the given files overwriting locked existing files.
	 * 
	 * @param	array		$files
	 */
	protected function checkFiles(&$files) {
		if ($this->fileHandler != null && $this->fileHandler instanceof IFileHandler) {
			$this->fileHandler->checkFiles($files);
		}
	}
	
	/**
	 * Logs the given files.
	 * 
	 * @param	array		$files
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
		FileUtil::makeWritable($target);
	}
}
