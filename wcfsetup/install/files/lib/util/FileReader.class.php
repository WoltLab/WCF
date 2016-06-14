<?php
namespace wcf\util;
use wcf\system\exception\SystemException;
use wcf\system\io\File;
use wcf\system\Regex;

/**
 * Provides functions to send files to the client via PHP.
 * 
 * @author	Sebastian Oettl, Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
class FileReader {
	/**
	 * http options
	 * @var	array
	 */
	protected $options = [
		'filename' => '',
		'mimeType' => 'application/octet-stream',
		'filesize' => 0,
		'showInline' => false,
		'enableRangeSupport' => true,
		'lastModificationTime' => 0,
		'expirationDate' => 0,
		'maxAge' => 0
	];
	
	/**
	 * list of header items
	 * @var	array
	 */
	protected $headers = [];
	
	/**
	 * start byte
	 * @var	integer
	 */
	protected $startByte = 0;
	
	/**
	 * end byte
	 * @var	integer
	 */
	protected $endByte = 0;
	
	/**
	 * True if http range is invalid.
	 * @var	boolean
	 */
	protected $invalidRange = false;
	
	/**
	 * Creates a new instance of the HTTPFileReader class.
	 * 
	 * @param	string		$location
	 * @param	array		$options
	 * @throws	SystemException
	 */
	public function __construct($location, array $options) {
		$this->location = $location;
		
		// check location
		if (empty($this->location) || !file_exists($this->location)) {
			throw new SystemException('Location of file is not set or invalid');
		}
		
		// set options
		$this->setOptions($options);
	}
	
	/**
	 * Sends the file to the client.
	 */
	public function send() {
		// set filename if necessary
		if (empty($this->options['filename'])) {
			$this->options['filename'] = basename($this->location);
		}
		
		// detect filesize if necessary
		if (empty($this->options['filesize'])) {
			$this->options['filesize'] = @filesize($this->location);
		}
		
		// prepare range and headers
		$this->handleRange();
		$this->handleHeaders();
		
		// send file to client
		$this->sendHeaders();
		if (!$this->invalidRange) {
			$this->sendFile();
		}
	}
	
	/**
	 * Handles the given range options.
	 */
	protected function handleRange() {
		$this->startByte = 0;
		$this->endByte = $this->options['filesize'] - 1;
		if ($this->options['enableRangeSupport']) {
			if (!empty($_SERVER['HTTP_RANGE'])) {
				$regex = new Regex('^bytes=(-?\d+)(?:-(\d+))?$');
				if ($regex->match($_SERVER['HTTP_RANGE'])) {
					$matches = $regex->getMatches();
					$first = intval($matches[1]);
					$last = (isset($matches[2]) ? intval($matches[2]) : 0);
					
					if ($first < 0) {
						// negative value; subtract from filesize
						$this->startByte = $this->options['filesize'] + $first;
					}
					else {
						$this->startByte = $first;
						if ($last > 0) {
							$this->endByte = $last;
						}
					}
				}
			}
		}
	}
	
	/**
	 * Handles the given header items.
	 */
	protected function handleHeaders() {
		if ($this->startByte < 0 || $this->startByte >= $this->options['filesize'] || $this->endByte >= $this->options['filesize']) {
			// invalid range given
			$this->addHeader('', 'HTTP/1.1 416 Requested Range Not Satisfiable');
			$this->addHeader('Accept-Ranges', 'bytes');
			$this->addHeader('Content-Range', 'bytes */'.$this->options['filesize']);
			$this->invalidRange = true;
		}
		else {
			// file type
			$this->addHeader('Content-Type', $this->options['mimeType']);
			
			// file name
			$this->addHeader('Content-disposition', ($this->options['showInline'] ? 'inline' : 'attachment').'; filename="'.$this->options['filename'].'"');
			
			// range
			if ($this->startByte > 0 || $this->endByte < $this->options['filesize'] - 1) {
				$this->addHeader('', 'HTTP/1.1 206 Partial Content');
				$this->addHeader('Content-Range', 'bytes '.$this->startByte.'-'.$this->endByte.'/'.$this->options['filesize']);
			}
			if ($this->options['enableRangeSupport']) {
				$this->addHeader('Accept-Ranges', 'bytes');
			}
			
			// send file size
			$this->addHeader('Content-Length', ($this->endByte + 1 - $this->startByte));
			
			// cache headers
			if ($this->options['maxAge']) {
				$this->addHeader('Cache-control', 'max-age='.$this->options['maxAge'].', private');
			}
			if ($this->options['expirationDate']) {
				$this->addHeader('Expires', gmdate('D, d M Y H:i:s', $this->options['expirationDate']).' GMT');
			}
			if ($this->options['lastModificationTime']) {
				$this->addHeader('Last-Modified', gmdate('D, d M Y H:i:s', $this->options['lastModificationTime']).' GMT');
			}
		}
	}
	
	/**
	 * Sends the headers of the file to the client.
	 */
	protected function sendHeaders() {
		foreach ($this->headers as $name => $value) {
			if (empty($name)) {
				@header($value);
			}
			else {
				@header($name.': '.$value);
			}
		}
	}
	
	/**
	 * Sends the actual file to the client.
	 */
	protected function sendFile() {
		if ($this->startByte > 0 || $this->endByte < $this->options['filesize'] - 1) {
			$file = new File($this->location, 'rb');
			if ($this->startByte > 0) $file->seek($this->startByte);
			while ($this->startByte <= $this->endByte) {
				$remainingBytes = $this->endByte - $this->startByte;
				$readBytes = ($remainingBytes > 1048576 ? 1048576 : $remainingBytes + 1);
				echo $file->read($readBytes);
				$this->startByte += $readBytes;
			}
			$file->close();
		}
		else {
			readfile($this->location);
		}
	}
	
	/**
	 * Sets the options for the http response.
	 * 
	 * @param	array		$options
	 */
	public function setOptions(array $options) {
		if (isset($options['mimeType']) && $options['mimeType'] == 'image/x-png') {
			$options['mimeType'] = 'image/png';
		}
		
		$this->options = array_merge($this->options, $options);
	}
	
	/**
	 * Adds the header with the given name and value.
	 * 
	 * @param	string		$name
	 * @param	string		$value
	 */	
	public function addHeader($name, $value) {
		$this->headers[$name] = $value;
	}
	
	/**
	 * Removes the header with the given name.
	 * 
	 * @param	string		$name
	 */
	public function removeHeader($name) {
		unset($this->headers[$name]);
	}
}
