<?php
namespace wcf\system\image\cover\photo;
use wcf\system\style\StyleHandler;

/**
 * Wrapper for cover photos with an automatic fallback to the global cover photo.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Image\Cover\Photo
 * @since       5.2
 */
class CoverPhotoImage {
	/**
	 * @var ICoverPhotoImage
	 */
	protected $coverPhotoImage;
	
	/**
	 * @var int[]
	 */
	protected $dimensions;
	
	/**
	 * @var ICoverPhotoImage
	 */
	protected static $defaultCoverPhotoImage;
	
	/**
	 * @param ICoverPhotoImage|null $coverPhotoImage
	 */
	public function __construct(ICoverPhotoImage $coverPhotoImage = null) {
		$this->coverPhotoImage = $coverPhotoImage ?: self::getDefaultCoverPhoto();
	}
	
	/**
	 * @return string
	 */
	public function getCaption() {
		return $this->coverPhotoImage->getCoverPhotoCaption();
	}
	
	/**
	 * @return int
	 */
	public function getHeight() {
		return $this->getDimensions()['height'];
	}
	
	/**
	 * @return string
	 */
	public function getLocation() {
		return $this->coverPhotoImage->getCoverPhotoLocation();
	}
	
	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->coverPhotoImage->getCoverPhotoUrl();
	}
	
	/**
	 * @return int
	 */
	public function getWidth() {
		return $this->getDimensions()['width'];
	}
	
	/**
	 * @return int[]
	 */
	protected function getDimensions() {
		if ($this->dimensions === null) {
			$this->dimensions = ['height' => 0, 'width' => 0];
			$dimensions = @getimagesize($this->getLocation());
			if (is_array($dimensions)) {
				$this->dimensions['width'] = $dimensions[0];
				$this->dimensions['height'] = $dimensions[1];
			}
		}
		
		return $this->dimensions;
	}
	
	/**
	 * @return ICoverPhotoImage
	 */
	protected static function getDefaultCoverPhoto() {
		if (self::$defaultCoverPhotoImage === null) {
			self::$defaultCoverPhotoImage = new class implements ICoverPhotoImage {
				public function getCoverPhotoCaption() {
					return '';
				}
				
				public function getCoverPhotoLocation() {
					return StyleHandler::getInstance()->getStyle()->getCoverPhotoLocation();
				}
				
				public function getCoverPhotoUrl() {
					return StyleHandler::getInstance()->getStyle()->getCoverPhotoUrl();
				}
				
			};
		}
		
		return self::$defaultCoverPhotoImage;
	}
}
