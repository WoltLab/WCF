<?php
namespace wcf\system\io;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Creates a tar file archive.
 * 
 * Usage:
 * ------
 * $tar = new TarWriter('archive.tar', true);
 * $tar->add(array('file1', 'file2'));
 * $tar->create();
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.io
 * @category	Community Framework
 */
class TarWriter extends Tar {
	/**
	 * @see	wcf\system\io\Tar::$mode
	 */
	protected $mode = 'wb+';
	
	/**
	 * Creates a new TarWriter object.
	 * 
	 * @param	string		$archiveName
	 * @param	boolean		$compress	enables gzip compression
	 */
	public function __construct($archiveName, $compress = false) {
		$this->archiveName = $archiveName;
		$this->isZipped = $compress;
		if ($compress) $this->mode = 'wb9'; // set compression level
		$this->open();
	}
	
	/**
	 * Writes the last 0 filled block for end of archive.
	 */
	protected function writeFooter() {
		if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
			$format = 'Z512';
		}
		else {
			$format = 'a512';
		}
		
		$this->file->write(pack($format, ''));
	}
	
	/**
	 * Creates the tar archive.
	 */
	public function create() {
		$this->writeFooter();
		$this->close();
	}
	
	/**
	 * Adds a string to the tar archive.
	 * 
	 * @param	string		$filename
	 * @param	string		$string		file content
	 * @return	boolean		result
	 */
	public function addString($filename, $string) {
		if (empty($filename)) return false;
		
		$filename = FileUtil::unifyDirSeparator($filename);
		
		if (!$this->writeHeaderBlock($filename, strlen($string), TIME_NOW, 33279)) {
			return false;
		}
		
		if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
			$format = 'Z512';
		}
		else {
			$format = 'a512';
		}
		
		$i = 0;
		while (($buffer = substr($string, (($i++) * 512), 512)) != '') {
			$this->file->write(pack($format, $buffer));
		}
		
		return true;
	}
	
	/**
	 * Adds a list of files or directories to the tar archive.
	 * 
	 * @param	mixed		$files
	 * @param	string		$addDir
	 * @param	string		$removeDir
	 * @return	boolean		result
	 */
	public function add($files, $addDir = '', $removeDir = '') {
		if (!is_array($files)) $files = array($files);
		if (empty($files)) return false;
		
		$result = true;
		
		// unify dir separator
		$addDir = FileUtil::unifyDirSeparator($addDir);
		$removeDir = FileUtil::unifyDirSeparator($removeDir);
		
		foreach ($files as $filename) {
			if (!$result) {
				break;
			}
			
			if (!$filename || $filename == $this->archiveName) {
				continue;
			}
			
			if (!file_exists($filename)) {
				throw new SystemException("Unable to find file '".$filename."'", 11002);
			}
			
			// add file
			if (!$this->addFile($filename, $addDir, $removeDir)) {
				return false;
			}
			
			// handle directories
			if (@is_dir($filename)) {
				$handle = opendir($filename);
				
				while (($dirFile = readdir($handle)) !== false) {
					if (($dirFile != '.') && ($dirFile != '..')) {
						if ($filename != ".") $dirFile = $filename.'/'.$dirFile;
						$result = $this->add($dirFile, $addDir, $removeDir);
					}
				}
				
				closedir($handle);
			}
		}
		
		return $result;
	}
	
	/**
	 * Adds a file to the tar archive.
	 * 
	 * @param	string		$filename
	 * @param	string		$addDir
	 * @param	string		$removeDir
	 * @return	boolean		result
	 */
	protected function addFile($filename, $addDir, $removeDir) {
		$filename = FileUtil::unifyDirSeparator($filename);
		$storedFilename = $filename;
		if (!empty($removeDir)) $storedFilename = StringUtil::replaceIgnoreCase($removeDir, '', $filename);
		if (!empty($addDir)) $storedFilename = $addDir . $storedFilename;
		
		if (is_file($filename)) {
			// open file
			$file = new File($filename, 'rb');
			
			// write header
			if (!$this->writeFileHeader($filename, $storedFilename)) {
				return false;
			}
			
			// write file content
			if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
				$format = 'Z512';
			}
			else {
				$format = 'a512';
			}
			
			while (($buffer = $file->read(512)) != '') {
				$this->file->write(pack($format, $buffer));
			}
			
			// close file
			$file->close();
		}
		else {
			// only directory header
			if (!$this->writeFileHeader($filename, $storedFilename)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Writes the file header.
	 * 
	 * @param	string		$filename
	 * @param	string		$storedFilename
	 * @return	boolean		result
	 */
	protected function writeFileHeader($filename, $storedFilename) {
		$fileInfo = stat($filename);
		$permissions = fileperms($filename);
		$mtime = filemtime($filename);
		if (@is_dir($filename)) {
			$typeFlag = '5';
			$size = 0;
		}
		else {
			$typeFlag = '';
			clearstatcache();
			$size = filesize($filename);
		}
		
		return $this->writeHeaderBlock($storedFilename, $size, $mtime, $permissions, $typeFlag, $fileInfo[4], $fileInfo[5]);
	}
	
	/**
	 * Writes header block.
	 * 
	 * @param	string		$filename
	 * @param	integer		$size
	 * @param	integer		$mtime
	 * @param	integer		$permissions
	 * @param	string		$typeFlag
	 * @param	integer		$uid
	 * @param	integer		$gid
	 * @return	boolean
	 */
	public function writeHeaderBlock($filename, $size, $mtime = 0, $permissions = 0, $typeFlag = '', $uid = 0, $gid = 0) {
		if (strlen($filename) > 99) {
			if (!$this->writeLongHeaderBlock($filename)) {
				return false;
			}
		}
		
		if ($typeFlag == "5") {
			$size = sprintf("%11s ", decOct(0));
		}
		else {
			$size = sprintf("%11s ", decOct($size));
		}
		
		$uid = sprintf("%6s ", decOct($uid));
		$gid = sprintf("%6s ", decOct($gid));
		$permissions = sprintf("%6s ", decOct($permissions));
		$mtime = sprintf("%11s", decOct($mtime));
		
		if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
			$format = 'Z100Z8Z8Z8Z12A12';
		}
		else {
			$format = 'a100a8a8a8a12A12';
		}
		
		$binaryDataFirst = pack($format, $filename, $permissions, $uid, $gid, $size, $mtime);
		
		if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
			$format = 'Z1Z100Z6Z2Z32Z32Z8Z8Z155Z12';
		}
		else {
			$format = 'a1a100a6a2a32a32a8a8a155a12';
		}
		
		$binaryDataLast = pack($format, $typeFlag, '', '', '', '', '', '', '', '', '');
		
		// calculate the checksum
		$checksum = 0;
		for ($i = 0; $i < 148; $i++) {
			$checksum += ord(substr($binaryDataFirst, $i, 1));
		}
		for ($i = 148; $i < 156; $i++) {
			$checksum += ord(' ');
		}
		for ($i = 156, $j = 0; $i < 512; $i++, $j++) {
			$checksum += ord(substr($binaryDataLast, $j, 1));
		}
		
		if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
			$format = 'Z8';
		}
		else {
			$format = 'a8';
		}
		
		$this->file->write($binaryDataFirst, 148);
		$this->file->write(pack($format, sprintf("%6s ", decOct($checksum))), 8); // write the checksum
		$this->file->write($binaryDataLast, 356);
		
		return true;
	}
	
	/**
	 * Writes a long header block.
	 * 
	 * @param	string		$filename
	 * @return	boolean
	 */
	protected function writeLongHeaderBlock($filename) {
		$size = sprintf("%11s ", decOct(strlen($filename)));
		$typeFlag = 'L';
		
		if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
			$format = 'Z100Z8Z8Z8Z12A12';
		}
		else {
			$format = 'a100a8a8a8a12A12';
		}
		
		$binaryDataFirst = pack($format, '././@LongLink', 0, 0, 0, $size, 0);
		
		if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
			$format = 'Z1Z100Z6Z2Z32Z32Z8Z8Z155Z12';
		}
		else {
			$format = 'a1a100a6a2a32a32a8a8a155a12';
		}
		
		$binaryDataLast = pack($format, $typeFlag, '', '', '', '', '', '', '', '', '');
		
		// calculate the checksum
		$checksum = 0;
		for ($i = 0; $i < 148; $i++) {
			$checksum += ord(substr($binaryDataFirst, $i, 1));
		}
		for ($i = 148; $i < 156; $i++) {
			$checksum += ord(' ');
		}
		for ($i = 156, $j = 0; $i < 512; $i++, $j++) {
			$checksum += ord(substr($binaryDataLast, $j, 1));
		}
		
		$this->file->write($binaryDataFirst, 148);
		
		if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
			$format = 'Z8';
		}
		else {
			$format = 'a8';
		}
		
		$this->file->write(pack($format, sprintf("%6s ", decOct($checksum))), 8); // write the checksum
		$this->file->write($binaryDataLast, 356);
		
		if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
			$format = 'Z512';
		}
		else {
			$format = 'a512';
		}
		
		$i = 0;
		while (($buffer = substr($filename, (($i++) * 512), 512)) != '') {
			$this->file->write(pack($format, $buffer));
		}
		
		return true;
	}
}
