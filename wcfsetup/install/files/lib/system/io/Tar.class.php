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
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Io
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
	protected $contentList = [];
	
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
	 * @var	File
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
	 * @throws	SystemException
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
	 * @inheritDoc
	 */
	public function getContentList() {
		if (!$this->read) {
			$this->open();
			$this->readContent();
		}
		return $this->contentList;
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
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
	 * @inheritDoc
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
		$content = $this->file->read($header['size']);
		
		if (strlen($content) != $header['size']) {
			throw new SystemException("Could not untar file '".$header['filename']."' to string. Maybe the archive is truncated?");
		}
		
		return $content;
	}
	
	/**
	 * @inheritDoc
	 */
	public function extract($index, $destination) {
		if (!$this->read) {
			$this->open();
			$this->readContent();
		}
		$header = $this->getFileInfo($index);
		
		FileUtil::makePath(dirname($destination));
		if ($header['type'] === 'folder') {
			FileUtil::makePath($destination);
			return;
		}
		if ($header['type'] === 'symlink') {
			// skip symlinks
			return;
		}
		
		// seek to offset
		$this->file->seek($header['offset']);
		
		$targetFile = new File($destination);
		
		// read and write data
		if ($header['size']) {
			$buffer = $this->file->read($header['size']);
			$targetFile->write($buffer);
		}
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
		$this->contentList = [];
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
				$format = 'Z'.$header['size'].'filename';
				
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
			
			$this->file->seek($this->file->tell() + (512 * ceil($header['size'] / 512)));
		}
	}
	
	/**
	 * Unpacks file header for one file entry.
	 * 
	 * @param	string		$binaryData
	 * @return	array|boolean
	 */
	protected function readHeader($binaryData) {
		if (strlen($binaryData) != 512) {
			return false;
		}
		
		$header = [];
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
		$format = 'Z100filename/Z8mode/Z8uid/Z8gid/Z12size/Z12mtime/Z8checksum/Z1typeflag/Z100link/Z6magic/Z2version/Z32uname/Z32gname/Z8devmajor/Z8devminor/Z155prefix';
		
		$data = unpack($format, $binaryData);
		
		// Extract the properties
		$header['checksum'] = @octdec(trim($data['checksum']));
		if ($header['checksum'] == $checksum) {
			$header['filename'] = trim($data['filename']);
			$header['mode'] = octdec(trim($data['mode']));
			$header['uid'] = octdec(trim($data['uid']));
			$header['gid'] = octdec(trim($data['gid']));
			$header['size'] = octdec(trim($data['size']));
			$header['mtime'] = octdec(trim($data['mtime']));
			$header['prefix'] = trim($data['prefix']);
			if ($header['prefix']) {
				$header['filename'] = $header['prefix'].'/'.$header['filename'];
			}
			$header['typeflag'] = $data['typeflag'];
			if ($header['typeflag'] == '5') {
				$header['size'] = 0;
				$header['type'] = 'folder';
			}
			else if ($header['typeflag'] == '2') {
				$header['type'] = 'symlink';
				$header['target'] = $data['link'];
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
