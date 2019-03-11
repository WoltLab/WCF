<?php
namespace wcf\system\setup;

/**
 * Special file handler used during setup to log the deployed files.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Setup
 */
class SetupFileHandler implements IFileHandler {
	/**
	 * list of installed files
	 * @var string[]
	 */
	protected $files;
	
	/**
	 * @inheritDoc
	 */
	public function checkFiles(array $files) {
		/* does nothing */
	}
	
	/**
	 * @inheritDoc
	 */
	public function logFiles(array $files) {
		$this->files = $files;
	}
	
	/**
	 * Writes the list of files to a log file.
	 * 
	 * @param       string          $filename
	 */
	public function dumpToFile($filename) {
		file_put_contents($filename, implode("\n", $this->files));
	}
}
