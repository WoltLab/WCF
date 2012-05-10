<?php
namespace wcf\system\io;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;

/**
 * Reads .zip-Files
 * 
 * @author	Tim Düsterhus
 * @copyright	2012 Tim Düsterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.io
 * @category 	Community Framework
 */
class Zip extends File {
	const LOCAL_FILE_SIGNATURE = "\x50\x4b\x03\x04";
	const CENTRAL_DIRECTORY_SIGNATURE = "\x50\x4b\x01\x02";
	const EOF_SIGNATURE = "\x50\x4b\x05\x06";
	
	/**
	 * @see \wcf\system\io\File
	 */
	public function __construct($filename) {
		parent::__construct($filename, 'rb');
	}
	
	/**
	 * Returns the offset for the given file and false of failure.
	 * 
	 * @param	string	$filename	file to search
	 * @return	mixed
	 */
	public function getOffsetByFilename($filename) {
		$this->jumpToCentralDirectory();
		$centralDirectory = $this->readCentralDirectory();
		
		if (isset($centralDirectory['files'][$filename])) return $centralDirectory['files'][$filename]['offset'];
		return false;
	}
	
	/**
	 * Extracts all files to the given destination.
	 * The directory-structure inside the .zip is preserved.
	 * 
	 * @param	string	$destination	where to extract
	 */
	public function extractAll($destination) {
		$destination = FileUtil::addTrailingSlash($destination);
		$this->seek(0);
		
		while ($this->isFile()) {
			$offset = $this->tell();
			$file = $this->readFile();
			$filename = $file['header']['filename'];
			$this->extract($offset, $destination.dirname($filename));
		}
	}
	
	/**
	 * Extracts the file at the given offset to a string.
	 * 
	 * @param	integer		$offset		The offset to extract
	 * @return	string
	 */
	public function extractToString($offset) {
		$file = $this->readFile($offset);
		
		return $file['content'];
	}
	
	/**
	 * Extracts the file at the given offset to the given destination.
	 * The directory-structure inside the .zip is ignored.
	 *
	 * @param	integer		$offset		The offset to extract
	 * @param	string		$destination	Where to extract
	 * @param	string		$filename	The filename. When not given the original filename is used
	 */
	public function extract($offset, $destination, $filename = null) {
		$file = $this->readFile($offset);
		$filename = ($filename ?: $file['header']['filename']);
		
		FileUtil::makePath($destination);
		$targetFile = new File(FileUtil::addTrailingSlash($destination).basename($filename));
		$targetFile->write($file['content'], strlen($file['content']));
		$targetFile->close();
		
		if (FileUtil::isApacheModule() || !@$targetFile->is_writable()) {
			@$targetFile->chmod(0777);
		}
		else {
			@$targetFile->chmod(0755);
		}
		
		if ($file['header']['x-timestamp']) {
			@$targetFile->touch($file['header']['x-timestamp']);
		}
		
		// check filesize
		if (filesize(FileUtil::addTrailingSlash($destination).basename($filename)) != $file['header']['uncompressedSize']) {
			throw new SystemException("Could not unzip file '".$file['header']['filename']."' to '".FileUtil::addTrailingSlash($destination).basename($filename)."'. Maybe disk quota exceeded in folder '".$destination."'.");
		}
	}
	
	/**
	 * Moves the file-pointer to the beginning of the Central Directory.
	 */
	public function jumpToCentralDirectory() {
		$this->seek(0);
		while ($this->isFile()) {
			$this->skipFile();
			$offset = $this->tell();
		}
		
		if ($this->read(4) !== self::CENTRAL_DIRECTORY_SIGNATURE) throw new SystemException('Unable to locate central directory');
		$this->seek($offset);
	}
	
	/**
	 * Reads the Central Directory and returns it.
	 * 
	 * @param	integer		$offset		where to start reading
	 * @return	array
	 */
	public function readCentralDirectory($offset = null) {
		if ($offset === null) $offset = $this->tell();
		
		$this->seek($offset);
		// check signature
		if ($this->read(4) !== self::CENTRAL_DIRECTORY_SIGNATURE) {
			throw new SystemException('Invalid offset passed to readCentralDirectory');
		}
		$this->seek($offset);
		
		$files = array();
		while ($this->read(4) === self::CENTRAL_DIRECTORY_SIGNATURE) {
			$data = unpack('vversion/vminVersion/vgeneralPurposeBit/vcompression/vmtime/vmdate', $this->read(12));
			// calculate timestamp
			$second = ($data['mtime'] & 31 /* 5 bits */) * 2;
			$minute = ($data['mtime'] >> 5) & 63 /* 6 bits */;
			$hour = ($data['mtime'] >> 11) & 31 /* 5 bits */;
			$day = $data['mdate'] & 31 /* 5 bits */;
			$month = ($data['mdate'] >> 5) & 15 /* 4 bits */;
			$year = (($data['mdate'] >> 9) & 127 /* 7 bits */) + 1980;
			$data['x-timestamp'] = mktime($hour, $minute, $second, $month, $day, $year);
			
			$data += unpack('Vcrc32/VcompressedSize/VuncompressedSize/vfilenameLength/vextraFieldLength/vfileCommentLength/vdiskNo/vinternalAttr/vexternalAttr', $this->read(26));
			$data['offset'] = $this->readAndUnpack(4, 'v');
			$data['filename'] = $this->read($data['filenameLength']);
			
			// read extraField
			if ($data['extraFieldLength'] > 0) $data['extraField'] = $this->read($data['extraFieldLength']);
			else $data['extraField'] = '';
			// read filecomment
			if ($data['fileCommentLength'] > 0) $data['fileComment'] = $this->read($data['fileCommentLength']);
			else $data['fileComment'] = '';
			
			$files[$data['filename']] = $data;
		}
		$this->seek($this->tell() - 4);
		$size = $this->tell() - $offset;
		
		if ($this->read(4) !== self::EOF_SIGNATURE) throw new SystemException('Could not find the end of Central Directory');
		
		$eof = unpack('vdiskNo/vdiskWithCentralDirectory/vdiskEntries/vtotalEntries/vcentralDirectorySize', $this->read(12));
		// check size of Central Directory
		if ($size !== $eof['centralDirectorySize']) throw new SystemException('Central Directory size does not match');
		$eof += unpack('vcentralDirectoryOffset/vcommentLength', $this->read(6));
		
		// read comment
		if ($eof['commentLength'] > 0) $eof['comment'] = $this->read($eof['commentLength']);
		else $eof['comment'] = '';
		
		return array('files' => $files, 'eof' => $eof);
	}
	
	/**
	 * Checks whether the next record is a file.
	 * This does not change the position of the file-pointer.
	 * 
	 * @param	integer		$offset		where to start reading
	 * @return	boolean
	 */
	public function isFile($offset = null) {
		if ($offset === null) $offset = $this->tell();
		
		$oldOffset = $this->tell();
		$this->seek($offset);
		// check signature
		$result = $this->read(4) === self::LOCAL_FILE_SIGNATURE;
		
		$this->seek($oldOffset);
		return $result;
	}
	
	/**
	 * Moves the file-pointer right after this file.
	 * 
	 * @param	integer		$offset		where to start reading
	 */
	public function skipFile($offset = null) {
		if ($offset === null) $offset = $this->tell();
		
		$this->seek($offset);
		// check signature
		if ($this->read(4) !== self::LOCAL_FILE_SIGNATURE) {
			throw new SystemException('Invalid offset passed to skipFile');
		}
		
		// skip unneccessary header
		$this->seek($offset + 18);
		// read compressed filesize
		$compressedSize = $this->readAndUnpack(4, 'V');
		$this->read(4);
		// read length of some fields
		$filenameLength = $this->readAndUnpack(2, 'v');
		$extraFieldLength = $this->readAndUnpack(2, 'v');
		
		// skip file
		$this->seek($offset + 30 + $compressedSize + $filenameLength + $extraFieldLength);
	}
	
	/**
	 * Reads a file and returns it.
	 *
	 * @param	integer		$offset		where to start reading
	 * @return	array
	 */
	public function readFile($offset = null) {
		if ($offset === null) $offset = $this->tell();
		
		$this->seek($offset);
		// check signature
		if ($this->read(4) !== self::LOCAL_FILE_SIGNATURE) {
			throw new SystemException('Invalid offset passed to readFile');
		}
		
		// read headers
		$header = array();
		$header = unpack('vminVersion/vgeneralPurposeBit/vcompression/vmtime/vmdate', $this->read(10));
		if ($header['generalPurposeBit'] & 7 /* 3rd bit */) throw new SystemException('Data Descriptors are not supported'); 
		$second = ($header['mtime'] & 31 /* 5 bits */) * 2;
		$minute = ($header['mtime'] >> 5) & 63 /* 6 bits */;
		$hour = ($header['mtime'] >> 11) & 31 /* 5 bits */;
		$day = $header['mdate'] & 31 /* 5 bits */;
		$month = ($header['mdate'] >> 5) & 15 /* 4 bits */;
		$year = (($header['mdate'] >> 9) & 127 /* 7 bits */) + 1980;
		$header['x-timestamp'] = mktime($hour, $minute, $second, $month, $day, $year);
		$header += unpack('Vcrc32/VcompressedSize/VuncompressedSize/vfilenameLength/vextraFieldLength', $this->read(16));
		
		// read filename
		$header['filename'] = $this->read($header['filenameLength']);
		// read extra field
		if ($header['extraFieldLength'] > 0) $header['extraField'] = $this->read($header['extraFieldLength']);
		else $header['extraField'] = '';
		
		// read contents
		$content = $this->read($header['compressedSize']);
		
		// uncompress file
		switch ($header['compression']) {
			case 8:
				$content = gzinflate($content);
			case 0:
			break;
			default:
				throw new SystemException('Compression '.$header['compression'].' is not supported');
		}
		
		// check crc32
		if (crc32($content) != $header['crc32']) throw new SystemException('Checksum does not match');
		
		return array('header' => $header, 'content' => $content);
	}
	
	/**
	 * Reads in the specified number of bytes and unpacks them.
	 * 
	 * @param	integer		$length		Number of bytes to read
	 * @param	string		$type		Which type are the bytes of
	 * @return	mixed
	 */
	protected function readAndUnpack($length, $type) {
		$data = unpack($type, $this->read($length));
		
		return $data[1];
	}
}
