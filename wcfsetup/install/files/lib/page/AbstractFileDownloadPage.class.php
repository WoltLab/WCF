<?php
namespace wcf\page;
use wcf\system\exception\SystemException;
use wcf\system\io\File;
use wcf\system\Regex;

/**
 * Abstract implementation of a page for downloading files.
 * 
 * @author	Sebastian Oettl, Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
abstract class AbstractFileDownloadPage extends AbstractPage {
	/**
	 * @see	wcf\page\IPage::$useTemplate
	 */
	public $useTemplate = false;
	
	/**
	 * location of the file
	 * @var	string
	 */
	public $location = '';
	
	/**
	 * filename of the file
	 * @var	string
	 */
	public $filename = '';
	
	/**
	 * mime type of the file
	 * @var	string
	 */	
	public $mimeType = 'application/octet-stream';
	
	/**
	 * filesize of the file
	 * @var	integer
	 */
	public $filesize = 0;
	
	/**
	 * True if the file should is displayed inline.
	 * @var	boolean
	 */
	public $showInline = false;
	
	/**
	 * True if range support is enabled.
	 * @var	boolean
	 */
	public $enableRangeSupport = true;
	
	/**
	 * identifier of the file
	 * @var	integer
	 */
	public $fileIdentifier = '';
	
	/**
	 * last modification time of the file
	 * @var	integer
	 */
	public $lastModificationTime = 0;
	
	/**
	 * @see	wcf\page\IPage::show()
	 */
	public function show() {
		parent::show();
		
		// check location
		if (empty($this->location) || !file_exists($this->location)) {
			throw new SystemException('Location of file is not set or invalid');
		}
		
		// set filename if necessary
		if (empty($this->filename)) {
			$this->filename = basename($this->location);
		}
		
		// detect filesize if necessary
		if (empty($this->filesize)) {
			$this->filesize = @filesize($this->location);
		}
		
		// set file identifier if necessary
		if (empty($this->fileIdentifier)) {
			$this->fileIdentifier = sha1_file($this->location);
		}
		
		// detect last modification time if necessary
		if (!$this->lastModificationTime) {
			$this->lastModificationTime = @filemtime($this->location);
		}
		
		// range support
		$startByte = 0;
		$endByte = $this->filesize - 1;
		if ($this->enableRangeSupport) {
			if (!empty($_SERVER['HTTP_RANGE'])) {
				$regex = new Regex('^bytes=(-?\d+)(?:-(\d+))?$');
				if ($regex->match($_SERVER['HTTP_RANGE'])) {
					$matches = $regex->getMatches();
					$first = intval($matches[1]);
					$last = (isset($matches[2]) ? intval($matches[2]) : 0);
					
					if ($first < 0) {
						// negative value; subtract from filesize
						$startByte = $this->filesize + $first;
					}
					else {
						$startByte = $first;
						if ($last > 0) {
							$endByte = $last;
						}
					}
					
					// validate given range
					if ($startByte < 0 || $startByte >= $this->filesize || $endByte >= $this->filesize) {
						// invalid range given
						@header('HTTP/1.1 416 Requested Range Not Satisfiable');
						@header('Accept-Ranges: bytes');
						@header('Content-Range: bytes */'.$this->filesize);
						exit;
					}
				}
			}
		}
		
		// send headers
		// file type
		if ($this->mimeType == 'image/x-png') $this->mimeType = 'image/png';
		@header('Content-Type: '.$this->mimeType);
		
		// file name
		@header('Content-disposition: '.($this->showInline ? 'inline' : 'attachment').'; filename="'.$this->filename.'"');
		
		// range
		if ($startByte > 0 || $endByte < $this->filesize - 1) {
			@header('HTTP/1.1 206 Partial Content');
			@header('Content-Range: bytes '.$startByte.'-'.$endByte.'/'.$this->filesize);
		}
		if ($this->enableRangeSupport) {
			@header('ETag: "'.$this->fileIdentifier.'"');
			@header('Accept-Ranges: bytes');
		}
		
		// send file size
		@header('Content-Length: '.($endByte + 1 - $startByte));
		
		// cache headers
		@header('Cache-control: max-age=31536000, private');
		@header('Expires: '.gmdate('D, d M Y H:i:s', TIME_NOW + 31536000).' GMT');
		@header('Last-Modified: '.gmdate('D, d M Y H:i:s', $this->lastModificationTime).' GMT');
		
		// show file
		if ($startByte > 0 || $endByte < $this->filesize - 1) {
			$file = new File($this->location, 'rb');
			if ($startByte > 0) $file->seek($startByte);
			while ($startByte <= $endByte) {
				$remainingBytes = $endByte - $startByte;
				$readBytes = ($remainingBytes > 1048576) ? 1048576 : $remainingBytes + 1;
				echo $file->read($readBytes);
				$startByte += $readBytes;
			}
			$file->close();
		}
		else {
			readfile($this->location);
		}
		exit;
	}
}
