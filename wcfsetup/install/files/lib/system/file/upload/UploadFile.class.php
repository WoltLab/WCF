<?php
namespace wcf\system\file\upload;
use wcf\util\FileUtil;

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
	 * @var String
	 */
	private $location;
	
	/**
	 * Indicator, whether the file is already processed.
	 * @var boolean 
	 */
	private $processed;
	
	/**
	 * The filename. 
	 * @var String
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
	 * @var String
	 */
	private $uniquieId;
	
	/**
	 * UploadFile constructor.
	 *
	 * @param       String          $location
	 * @param       String          $filename
	 * @param       boolean         $viewableImage
	 * @param       boolean         $processed
	 */
	public function __construct($location, $filename, $viewableImage = true, $processed = false) {
		if (!file_exists($location)) {
			throw new \InvalidArgumentException("File '". $location ."' could not be found.");
		}
		
		$this->location = $location;
		$this->filename = $filename;
		$this->filesize = filesize($location);
		$this->processed = $processed;
		$this->viewableImage = $viewableImage;
		$this->uniquieId = sha1(sha1_file($location) . sha1($location));
		
		if (@getimagesize($location) !== false) {
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
	 * @return String|null
	 */
	public function getImage() {
		if (!$this->isImage() || !$this->viewableImage) {
			return null;
		}
		
		if (!$this->processed) {
			$imageData = getimagesize($this->location);
			return 'data:'. $imageData['mime'] .';base64,'.base64_encode(file_get_contents($this->location));
		}
		else {
			return $this->location;
		}
	}
	
	/**
	 * Returns the location of the file.
	 * 
	 * @return String
	 */
	public function getLocation() {
		return $this->location;
	}
	
	/**
	 * Returns the filename of the file. 
	 * 
	 * @return String
	 */
	public function getFilename() {
		return $this->filename;
	}
	
	/**
	 * Returns the unique file id for the file. It is used to identify the certain file. 
	 * @return String
	 */
	public function getUniqueFileId() {
		return $this->uniquieId;
	}
	
	/**
	 * Sets the new location of the file, after it is processed and 
	 * sets the `processed` attribute to true.
	 * 
	 * @param       String        $newLocation
	 */
	public function setProcessed($newLocation) {
		if (!file_exists($newLocation)) {
			throw new \InvalidArgumentException("File '". $newLocation ."' could not be found.");
		}
		
		$this->location = $newLocation;
		$this->processed = true;
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
