<?php
namespace wcf\system\devtools\package;
use wcf\system\io\Tar;

class DevtoolsTar extends Tar {
	protected $files = '';
	
	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct(array $files) {
		$this->files = $files;
	}
	
	public function registerFile($filename, $fullPath) {
		$this->files[$filename] = $fullPath;
	}
	
	public function getIndexByFilename($filename) {
		return (isset($this->files[$filename]) ? $filename : false);
	}
	
	public function extractToString($index) {
		if (!isset($this->files[$index])) {
			throw new \RuntimeException("DevtoolsTar does not permit reading any files except for the explicitly registered ones.");
		}
		
		return file_get_contents($this->files[$index]);
	}
	
	public function extract($index, $destination) {
		copy($this->files[$index], $destination);
	}
}
