<?php
namespace wcf\system\io;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;

/**
 * Opens tar or tar.gz archives.
 * 
 * Usage:
 * ------
 * $tar = new Tar('archive.tar');
 * $contentList = $tar->getContentList();
 * foreach ($contentList as $key => $val) {
 * 	$tar->extract($key, DESTINATION);
 * }
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.io
 * @category	Community Framework
 */
class Tar implements IArchive {
	/**
	 * name of the archive
	 * @var	string
	 */
	protected $archiveName = '';
	
	/**
	 * content of the tar file
	 * @var	array
	 */
	protected $contentList = array();
	
	/**
	 * indicates if tar file is opened
	 * @var	boolean
	 */
	protected $opened = false;
	
	/**
	 * indicates if file content has been read
	 * @var	boolean
	 */
	protected $read = false;
	
	/**
	 * file object
	 * @var	\wcf\system\io\File
	 */
	protected $file = null;
	
	/**
	 * indicates if the tar file is (g)zipped
	 * @var	boolean
	 */
	protected $isZipped = false;
	
	/**
	 * file access mode
	 * @var	string
	 */
	protected $mode = 'rb';
	
	/**
	 * chunk size for extracting
	 * @var	integer
	 */
	const CHUNK_SIZE = 8192;
	
	/**
	 * Creates a new Tar object.
	 * archiveName must be tarball or gzipped tarball
	 * 
	 * @param	string		$archiveName
	 */
	public function __construct($archiveName) {
		if (!is_file($archiveName)) {
			throw new SystemException("unable to find tar archive '".$archiveName."'");
		}
		
		$this->archiveName = $archiveName;
		$this->open();
		$this->readContent();
	}
	
	/**
	 * Destructor of this class, closes tar archive.
	 */
	public function __destruct() {
		$this->close();
	}
	
	/**
	 * Opens the tar archive and stores filehandle.
	 */
	public function open() {
		if (!$this->opened) {
			if ($this->isZipped) $this->file = new GZipFile($this->archiveName, $this->mode);
			else {
				// test compression
				$this->file = new File($this->archiveName, $this->mode);
				if ($this->file->read(2) == "\37\213") {
					$this->file->close();
					$this->isZipped = true;
					$this->file = new GZipFile($this->archiveName, $this->mode);
				}
				else {
					$this->file->seek(0);
				}
			}
			$this->opened = true;
		}
	}
	
	/**
	 * Closes the opened file.
	 */
	public function close() {
		if ($this->opened) {
			$this->file->close();
			$this->opened = false;
		}
	}
	
	/**
	 * @see	\wcf\system\io\IArchive::getContentList()
	 */
	public function getContentList() {
		if (!$this->read) {
			$this->open();
			$this->readContent();
		}
		return $this->contentList;
	}
	
	/**
	 * @see	\wcf\system\io\IArchive::getFileInfo()
	 */
	public function getFileInfo($fileIndex) {
		if (!is_int($fileIndex)) {
			$fileIndex = $this->getIndexByFilename($fileIndex);
		}
		
		if (!isset($this->contentList[$fileIndex])) {
			throw new SystemException("Tar: could find file '".$fileIndex."' in archive");
		}
		return $this->contentList[$fileIndex];
	}
	
	/**
	 * @see	\wcf\system\io\IArchive::getIndexByFilename()
	 */
	public function getIndexByFilename($filename) {
		foreach ($this->contentList as $index => $file) {
			if ($file['filename'] == $filename) {
				return $index;
			}
		}
		return false;
	}
	
	/**
	 * @see	\wcf\system\io\IArchive::extractToString()
	 */
	public function extractToString($index) {
		if (!$this->read) {
			$this->open();
			$this->readContent();
		}
		$header = $this->getFileInfo($index);
		
		// can not extract a folder
		if ($header['type'] != 'file') {
			return false;
		}
		
		// seek to offset
		$this->file->seek($header['offset']);
		
		// read data
		$content = '';
		$n = floor($header['size'] / 512);
		for ($i = 0; $i < $n; $i++) {
			$content .= $this->file->read(512);
		}
		if (($header['size'] % 512) != 0) {
			$buffer = $this->file->read(512);
			$content .= substr($buffer, 0, ($header['size'] % 512));
		}
		
		return $content;
	}
	
	/**
	 * @see	\wcf\system\io\IArchive::extract()
	 */
	public function extract($index, $destination) {
		if (!$this->read) {
			$this->open();
			$this->readContent();
		}
		$header = $this->getFileInfo($index);
		
		// check file size
		if (!$header['size']) {
			throw new SystemException("Could not untar file '".$header['filename']."', file is empty.");
		}
		
		FileUtil::makePath(dirname($destination));
		if ($header['type'] === 'folder') {
			FileUtil::makePath($destination);
			return;
		}
		
		// seek to offset
		$this->file->seek($header['offset']);
		
		$targetFile = new File($destination);
		
		// read and write data
		$buffer = $this->file->read($header['size']);
		$targetFile->write($buffer);
		$targetFile->close();
		
		FileUtil::makeWritable($destination);
		
		if ($header['mtime']) {
			@$targetFile->touch($header['mtime']);
		}
		
		// check filesize
		if (filesize($destination) != $header['size']) {
			throw new SystemException("Could not untar file '".$header['filename']."' to '".$destination."'. Maybe disk quota exceeded in folder '".dirname($destination)."'.");
		}
		
		return true;
	}
	
	/**
	 * Reads table of contents (TOC) from tar archive.
	 * This does not get the entire to memory but only parts of it.
	 */
	protected function readContent() {
		$this->contentList = array();
		$this->read = true;
		$i = 0;
		
		// Read the 512 bytes header
		$longFilename = null;
		while (strlen($binaryData = $this->file->read(512)) != 0) {
			// read header
			$header = $this->readHeader($binaryData);
			if ($header === false) {
				continue;
			}
			
			// fixes a bug that files with long names aren't correctly
			// extracted
			if ($longFilename !== null) {
				$header['filename'] = $longFilename;
				$longFilename = null;
			}
			if ($header['typeflag'] == 'L') {
				if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
					$format = 'Z'.$header['size'].'filename';
				}
				else {
					$format = 'a'.$header['size'].'filename';
				}
				
				$fileData = unpack($format, $this->file->read(512));
				$longFilename = $fileData['filename'];
				$header['size'] = 0;
			}
			// don't include the @LongLink file in the content list
			else {
				$this->contentList[$i] = $header;
				$this->contentList[$i]['index'] = $i;
				$i++;
			}
			
			$this->file->seek($this->file->tell() + (512 * ceil(($header['size'] / 512))));
		}
	}
	
	/**
	 * Unpacks file header for one file entry.
	 * 
	 * @param	string		$binaryData
	 * @return	array		$fileheader
	 */
	protected function readHeader($binaryData) {
		if (strlen($binaryData) != 512) {
			return false;
		}
		
		$header = array();
		$checksum = 0;
		// First part of the header
		for ($i = 0; $i < 148; $i++) {
			$checksum += ord(substr($binaryData, $i, 1));
		}
		// Calculate the checksum
		// Ignore the checksum value and replace it by ' ' (space)
		for ($i = 148; $i < 156; $i++) {
			$checksum += ord(' ');
		}
		// Last part of the header
		for ($i = 156; $i < 512; $i++) {
			$checksum += ord(substr($binaryData, $i, 1));
		}
		
		// extract values
		if (version_compare(PHP_VERSION, '5.5.0-dev', '>=')) {
			$format = 'Z100filename/Z8mode/Z8uid/Z8gid/Z12size/Z12mtime/Z8checksum/Z1typeflag/Z100link/Z6magic/Z2version/Z32uname/Z32gname/Z8devmajor/Z8devminor/Z155prefix';
		}
		else {
			$format = 'a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix';
		}
		
		$data = unpack($format, $binaryData);
		
		// Extract the properties
		$header['checksum'] = octDec(trim($data['checksum']));
		if ($header['checksum'] == $checksum) {
			$header['filename'] = trim($data['filename']);
			$header['mode'] = octDec(trim($data['mode']));
			$header['uid'] = octDec(trim($data['uid']));
			$header['gid'] = octDec(trim($data['gid']));
			$header['size'] = octDec(trim($data['size']));
			$header['mtime'] = octDec(trim($data['mtime']));
			$header['prefix'] = trim($data['prefix']);
			if ($header['prefix']) {
				$header['filename'] = $header['prefix'].'/'.$header['filename'];
			}
			if (($header['typeflag'] = $data['typeflag']) == '5') {
				$header['size'] = 0;
				$header['type'] = 'folder';
			}
			else {
				$header['type'] = 'file';
			}
			$header['offset'] = $this->file->tell();
			
			return $header;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Returns true if this tar is (g)zipped.
	 * 
	 * @return	boolean
	 */
	public function isZipped() {
		return $this->isZipped;
	}
}
