<?php
namespace wcf\system\devtools\package;
use wcf\system\io\Tar;

/**
 * Specialized implementation to emulate a regular package installation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Package
 * @since       3.1
 */
class DevtoolsTar extends Tar {
	/**
	 * list of virtual files
	 * @var string[]
	 */
	protected $files = [];
	
	/** @noinspection PhpMissingParentConstructorInspection @inheritDoc */
	public function __construct(array $files) {
		$this->files = $files;
	}
	
	/**
	 * Resets the internal file list for re-use, because the devtools use
	 * the same instance over and over to avoid some otherwise awkward
	 * changes to the code. 
	 */
	public function reset() {
		$this->contentList = $this->files = [];
		$this->read = false;
	}
	
	/**
	 * Registers a new file in the virtual file list.
	 * 
	 * @param       string          $filename
	 * @param       string          $fullPath
	 */
	public function registerFile($filename, $fullPath) {
		$this->files[$filename] = $fullPath;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getIndexByFilename($filename) {
		return (isset($this->files[$filename]) ? $filename : false);
	}
	
	/**
	 * @inheritDoc
	 */
	public function extractToString($index) {
		if (!isset($this->files[$index])) {
			throw new \RuntimeException("DevtoolsTar does not permit reading any files except for the explicitly registered ones.");
		}
		
		return file_get_contents($this->files[$index]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function extract($index, $destination) {
		copy($this->files[$index], $destination);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContentList() {
		if (!$this->read) {
			foreach ($this->files as $filename => $fullPath) {
				if (strpos($filename, '/') !== false) {
					$directory = dirname($filename) . '/';
					if (!isset($this->contentList[$directory])) {
						$this->contentList[$directory] = [
							'filename' => $directory,
							'type' => 'folder'
						];
					}
				}
				
				$this->contentList[$filename] = [
					'filename' => $filename,
					'type' => 'file'
				];
			}
			
			$this->read = true;
		}
		
		return $this->contentList;
	}
}
