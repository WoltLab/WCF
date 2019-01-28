<?php
namespace wcf\system\file\upload;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * An specific upload file.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\File\Upload
 * @since       5.2
 */
class UploadFile {
	/**
	 * Location for the file. 
	 * @var string
	 */
	private $location;
	
	/**
	 * Full image link. 
	 * @var string
	 */
	private $imageLink;
	
	/**
	 * Flag whether svg files should detected as image
	 * @var bool
	 */
	private $detectSvgAsImage;
	
	/**
	 * Indicator, whether the file is already processed.
	 * @var boolean 
	 */
	private $processed;
	
	/**
	 * The filename. 
	 * @var string
	 */
	private $filename;
	
	/**
	 * Indicator, whether the file is an image.
	 * @var boolean 
	 */
	private $isImage;
	
	/**
	 * Indicator, whether the file can be displayed as an image.
	 * @var boolean
	 */
	public $viewableImage;
	
	/**
	 * The filesize of the file.
	 * @var int 
	 */
	public $filesize;
	
	/**
	 * The unique id for the file.
	 * @var string
	 */
	private $uniqueId;
	
	/**
	 * UploadFile constructor.
	 *
	 * @param       string          $location
	 * @param       string          $filename
	 * @param       boolean         $viewableImage
	 * @param       boolean         $processed
	 * @param       boolean         $detectSvgAsImage
	 */
	public function __construct($location, $filename, $viewableImage = true, $processed = false, $detectSvgAsImage = false) {
		if (!file_exists($location)) {
			throw new \InvalidArgumentException("File '". $location ."' could not be found.");
		}
		
		$this->location = $location;
		$this->filename = $filename;
		$this->filesize = filesize($location);
		$this->processed = $processed;
		$this->viewableImage = $viewableImage;
		$this->uniqueId = StringUtil::getRandomID();
		$this->detectSvgAsImage = $detectSvgAsImage;
		
		if (@getimagesize($location) !== false || ($detectSvgAsImage && in_array(FileUtil::getMimeType($location), [
				'image/svg',
				'image/svg+xml'
			]))) {
			$this->isImage = true;
		}
	}
	
	/**
	 * Returns true, whether this file is an image.
	 * 
	 * @return boolean
	 */
	public function isImage() {
		return $this->isImage;
	}
	
	/**
	 * Returns the image location or a base64 encoded string of the image. Returns null
	 * if the file is not an image or the image is not viewable. 
	 * 
	 * @return string|null
	 */
	public function getImage() {
		if (!$this->isImage() || !$this->viewableImage) {
			return null;
		}
		
		if ($this->processed) {
			if ($this->imageLink === null) {
				// try to guess path
				return str_replace(WCF_DIR, WCF::getPath(), $this->location);
			}
			else {
				return $this->imageLink;
			}
		}
		else {
			$imageData = @getimagesize($this->location);
			
			if ($imageData !== false) {
				return 'data:'. $imageData['mime'] .';base64,'.base64_encode(file_get_contents($this->location));
			}
			
			if ($this->detectSvgAsImage && in_array(FileUtil::getMimeType($this->location), [
					'image/svg',
					'image/svg+xml'
				])) {
				return 'data:image/svg+xml;base64,'.base64_encode(file_get_contents($this->location));
			}
			
			throw new \LogicException('File is an image, but can not be rendered.8');
		}
	}
	
	/**
	 * Returns the location of the file.
	 * 
	 * @return string
	 */
	public function getLocation() {
		return $this->location;
	}
	
	/**
	 * Returns the filename of the file. 
	 * 
	 * @return string
	 */
	public function getFilename() {
		return $this->filename;
	}
	
	/**
	 * Returns the unique file id for the file. It is used to identify the certain file. 
	 * @return string
	 */
	public function getUniqueFileId() {
		return $this->uniqueId;
	}
	
	/**
	 * Sets the new location of the file, after it is processed and 
	 * sets the `processed` attribute to true.
	 * 
	 * @param       string        $newLocation
	 */
	public function setProcessed($newLocation) {
		if (!file_exists($newLocation)) {
			throw new \InvalidArgumentException("File '". $newLocation ."' could not be found.");
		}
		
		$this->location = $newLocation;
		$this->processed = true;
	}
	
	/**
	 * Sets the new image link of the file for processed files.
	 *
	 * @param       string        $link
	 */
	public function setImageLink($link) {
		$this->imageLink = $link;
	}
	
	/**
	 * Returns true, if the file is already processed. 
	 * 
	 * @return boolean
	 */
	public function isProcessed() {
		return $this->processed;
	}
	
	/**
	 * Returns icon name for this attachment.
	 *
	 * @return      string
	 */
	public function getIconName() {
		if ($iconName = FileUtil::getIconNameByFilename($this->filename)) {
			return 'file-' . $iconName . '-o';
		}
		
		return 'paperclip';
	}
}
