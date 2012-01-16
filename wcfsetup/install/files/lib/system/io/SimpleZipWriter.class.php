<?php
namespace wcf\system\io;
use wcf\system\exception\SystemException;
use wcf\system\io\File;
use wcf\system\io\ZipWriter;

/**
 * Creates zip archive.
 *
 * Usage:
 * ------
 * $zip = new ZipArch('archive.zip');
 * $zip->addFileFromPath('fileToAdd.txt');
 * $zip->saveFile();
 *
 * @author	H3llGhost
 * @copyright	2011
 * @package	com.woltlab.wcf
 * @subpackage	system.io
 * @category 	Community Framework
 */
class SimpleZipWriter extends ZipWriter {
	/**
	 * Saves the whole file path.
	 *
	 * @var string
	 */
	protected $filename = '';

	/**
	 * Creates a new ZipArchWriter object.
	 *
	 * @param 	string 		$filename file path for the archive
	 */
	public function __construct($filename) {
		$this->filename = $filename;
	}

	/**
	 * Add a file with the full file path.
	 * 
	 * @param string $file file path
	 * @param integer $time file creation time as unix timestamp
	 */
	public function addFileFromPath($file, $time = 0) {
		$f = new File($file, 'r');

		if ($time == 0) {
			$time = $f->filectime();
		}

		$content = $this->readFile($f);

		parent::addFile($content($file), $f->basename(), $time);

		$f->close();
	}

	/**
	 * Add a file with the full file path and the specified name.
	 * 
	 * @param string $file file path
	 * @param string $name file name
	 * @param integer $time file creation time as unix timestamp
	 */
	public function addFileFromPathAndName($file, $name, $time = 0) {
		$f = new File($file, 'r');

		if ($time == 0) {
			$time = $f->filectime();
		}

		$content = $this->readFile($f);

		parent::addFile($content, $name, $time);

		$f->close();
	}

	/**
	 * Reads the given file object and returns its content as string.
	 * 
	 * @param File $f fileobject
	 * @return string content as string
	 */
	protected function readFile($f) {
		// read data
		$content = '';
		$size = $f->filesize();
		$n = floor($size / 512);
		for ($i = 0; $i < $n; $i++) {
			$content .= $f->read(512);
		}
		if (($size % 512) != 0) {
			$buffer = $f->read(512);
			$content .= substr($buffer, 0, ($size % 512));
		}

		return $content;
	}

	/**
	 * Writes the content of the zip file.
	 */
	public function saveFile() {
		$f = new File($this->filename);

		$f->write($this->getFile());

		$f->close();
	}
}
