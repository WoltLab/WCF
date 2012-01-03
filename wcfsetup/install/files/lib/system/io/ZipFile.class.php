<?php
namespace wcf\system\io;
use wcf\system\exception\SystemException;
use wcf\system\io\File;

/**
 * Opens zip archive.
 *
 * Usage:
 * ------
 * $zip = new ZipArch('archive.zip');
 * $contentList = $zip->unzip(DESTINATION);
 *
 * @author	H3llGhost
 * @copyright	2011
 * @package	com.woltlab.wcf
 * @subpackage	system.io
 * @category 	Community Framework
 */
class ZipArch {
	/**
	 * The archive name.
	 * 
	 * @var string
	 */
	protected $archiveName = '';
	/**
	 * The array which contains all files from the archive.
	 * 
	 * @var array
	 */
	protected $contentList = null;
	/**
	 * The file object of the archive.
	 * 
	 * @var File
	 */
	protected $file = null;
	/**
	 * File mode.
	 * 
	 * @var string
	 */
	protected $mode = 'rb';
	/**
	 * Indicates if the archive is opened.
	 * 
	 * @var boolean
	 */
	protected $opened = false;
	/**
	 * Indicates if the archive was read.
	 * 
	 * @var boolean
	 */
	protected $read = false;

	/**
	 * Creates a new ZipArch object.
	 * archiveName must be zipped file
	 *
	 * @param 	string 		$archiveName
	 * @param	string		$mode
	 */
	public function __construct($archiveName, $mode = 'rb') {
		$match = array();
		if (!is_file($archiveName)) {
			throw new SystemException("unable to find zip archive '".$archiveName."'");
		}

		$this->archiveName = $archiveName;
		if (!empty($mode)) {
			$this->mode = $mode;
		}
		
		$this->open();
		$this->getContentList();
	}

	/**
	 * Destructor of this class, closes zip archive.
	 */
	public function __destruct() {
		$this->close();
	}

	/**
	 * Opens the zip archive and stores filehandle.
	 */
	protected function open() {
		if (!$this->opened) {
			$this->file = new File($this->archiveName, $this->mode);
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
	 * Returns the table of contents (TOC) list for this zip archive.
	 *
	 * @return 	array 		list of content
	 */
	public function getContentList() {
		if (!$this->read) {
			$this->open();
			$this->readContent();
		}
		return $this->contentList;
	}

	/**
	 * Reads table of contents (TOC) from zip archive.
	 * This does not get the entire to memory but only parts of it.
	 */
	protected function readContent() {
		$this->contentList = array();
		$this->read = false;

		for($i = 0; $i < 1024 && !$this->read; $i++) {
			$this->file->seek(-22-$i, SEEK_END);

			$signature = $this->file->read(4);
			if($signature == "\x50\x4b\x05\x06") {
				$this->file->seek(12, SEEK_CUR);
				$offset = unpack('V', $this->file->read(4));
				$len = unpack('v', $this->file->read(2));
				if($len[1]) $this->file->seek($len[1], SEEK_CUR);
				$this->file->seek($offset[1]);
				$signature = $this->file->read(4);

				$entries = array();
				while($signature == "\x50\x4b\x01\x02") {
					$this->file->seek(6, SEEK_CUR);
					$mode = unpack('v', $this->file->read(2));
					$this->file->seek(8, SEEK_CUR);
					$csize = unpack('V', $this->file->read(4));
					$usize = unpack('V', $this->file->read(4));
					$len1 = unpack('v', $this->file->read(2));
					$len2 = unpack('v', $this->file->read(2));
					$len3 = unpack('v', $this->file->read(2));
					$this->file->seek(8, SEEK_CUR);
					$offset = unpack('V', $this->file->read(4));
					$filename = $this->file->read($len1[1]);
					$this->file->seek($len2[1] + $len3[1], SEEK_CUR);

					$entries[$filename] = array('mode'=>$mode[1], 'csize'=>$csize[1], 'usize'=>$usize[1], 'offset'=>$offset[1]);
					$signature = $this->file->read(4);
				}

				foreach($entries as $filename=>$details) {
					$this->file->seek($details['offset']);
					$signature = $this->file->read(4);
					$offset = 0;
					if($signature == "\x50\x4b\x03\x04") {
						$this->file->seek(22, SEEK_CUR);
						$len1 = unpack('v', $this->file->read(2));
						$len2 = unpack('v', $this->file->read(2));
						$this->file->seek($len1[1] + $len2[1], SEEK_CUR);
						$offset = $this->file->tell();
					}
					$this->file->seek($details['csize'], SEEK_CUR);

					$this->contentList[$filename]['mode']   = $details['mode'];
					$this->contentList[$filename]['csize']  = $details['csize'];
					$this->contentList[$filename]['usize']  = $details['usize'];
					$this->contentList[$filename]['offset'] = $offset;
				}

				$this->read = true;
			}
		}
	}

	/**
	 * Unzip the whole archive to specified path
	 *
	 * @param string $destination
	 */
	function unzip($destination='./') {
		$ret = true;
		if(substr($destination, -1) != '/') $destination .= '/';
		foreach($this->contentList as $filename=>$info) {
			if(substr($filename, -1, 1) != '/') $ret = $ret && $this->extract($filename, "{$destination}/{$filename}");
		}
		return $ret;
	}

	/**
	 * Extract specified file out of the zip archive to the destination.
	 *
	 * @param string $file
	 * @param string $dst
	 */
	function extract($file, $dst) {
		if(!isset($this->contentList[$file])) return false; // doesn't exist
		if(substr($file, -1) == '/') return false; // can't extract a directory

		$dir = dirname($dst);
		if(!is_dir($dir)) {
			$path = '';
			$folders = explode('/', $dir);
			foreach($folders as $folder) {
				$path = $path ? "{$path}/{$folder}" : $folder;
				if(!is_dir($path)) mkdir($path);
			}
		}

		$targetFile = new File($dst);

		$info = &$this->contentList[$file];
		if($info['usize'] == 0) {
			$targetFile->write(''); // empty file
		}
		else
		{
			$this->file->seek($info['offset']);
			$content = $this->file->read($info['csize']);

			switch($info['mode']) {
				case 0: $targetFile->write($content); return true; // Not Compressed
				case 8: $targetFile->write(gzinflate($content, $info['usize'])); return true; // Deflate
				case 12: $targetFile->write(bzdecompress($content)); return true; // Bzip2
				default: throw new SystemException("Not supported zip mode for file ".$file."!"); // 1: Shrunk, 2-5: Factor ($mode - 1), 6: Implode, 7: Tokenizing, 9: Enhanced Deflate, 10: PKWARE, 18: IBM TERSE
			}
		}

		$targetFile->close();
		if (IS_APACHE_MODULE || !@$targetFile->is_writable()) {
			@$targetFile->chmod(0777);
		}
		else {
			@$targetFile->chmod(0755);
		}

		if ($header['mtime']) {
			@$targetFile->touch($header['mtime']);
		}

		// check filesize
		if (filesize($destination) != $header['size']) {
			throw new SystemException("Could not unzip file '".$header['filename']."' to '".$destination."'. Maybe disk quota exceeded in folder '".dirname($destination)."'.");
		}

		return true;
	}
}