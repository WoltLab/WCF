<?php
namespace wcf\system\image\adapter;
use wcf\system\exception\SystemException;

/**
 * Image adapter for ImageMagick imaging library.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.image.adapter
 * @category 	Community Framework
 */
class ImagickImageAdapter implements IImageAdapter {
	/**
	 * active color
	 * @var	\ImagickPixel
	 */
	protected $color = null;
	
	/**
	 * Imagick object
	 * @var	\Imagick
	 */
	protected $imagick = null;
	
	/**
	 * image height
	 * @var	integer
	 */	
	protected $height = 0;
	
	/**
	 * image width
	 * @var	integer
	 */	
	protected $width = 0;
	
	/**
	 * Creates a new ImagickImageAdapter.
	 */
	public function __construct() {
		$this->imagick = new \Imagick();
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::load()
	 */
	public function load($image, $type = '') {
		if (!($image instanceof \Imagick)) {
			throw new SystemException("Object must be an instance of Imagick");
		}
		
		$this->imagick = $imagick; //TODO: undefined variable
		$this->height = $this->imagick->getImageHeight();
		$this->width = $this->imagick->getImageWidth();
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::loadFile()
	 */
	public function loadFile($file) {
		try {
			$this->imagick->readImage($file);
		}
		catch (\ImagickException $e) {
			throw new SystemException("Image '".$file."' is not readable or does not exist.");
		}
		$this->height = $this->imagick->getImageHeight();
		$this->width = $this->imagick->getImageWidth();
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::createThumbnail()
	 */
	public function createThumbnail($maxWidth, $maxHeight, $obtainDimensions = true) {
		$thumbnail = $this->imagick;
		
		$thumbnail = $this->imagick;
		$thumbnail->cropThumbnailImage($maxWidth, $maxHeight);
		
		return $thumbnail;
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::clip()
	 */
	public function clip($originX, $originY, $width, $height) {
		$this->imagick->cropImage($width, $height, $originX, $originY);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::drawRectangle()
	 */
	public function drawRectangle($startX, $startY, $endX, $endY, $color) {
		$draw = new \ImagickDraw();
		$draw->setFillColor($this->color);
		$draw->setStrokeColor($this->color);
		$draw->rectangle($startX, $startY, $endX, $endY);
		
		$this->imagick->drawImage($draw);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::drawText()
	 */
	public function drawText($string, $x, $y, $color, $font = 4) {
		$draw = new \ImagickDraw();
		$draw->setFillColor($this->color);
		$draw->setTextAntialias(true);
		
		// draw text
		$draw->annotation($x, $y, $string);
		$this->imagick->drawImage($draw);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::setColor()
	 */
	public function setColor($red, $green, $blue) {
		$this->color = new \ImagickPixel();
		$this->color->setColor('rgb('.$red.','.$green.','.$blue.')');
		
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::hasColor()
	 */
	public function hasColor() {
		if ($this->color instanceof \ImagickPixel) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::getImage()
	 */
	public function getImage() {
		return $this->imagick;
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::writeImage()
	 */
	public function writeImage($image, $filename) {
		if (!($image instanceof \Imagick)) {
			throw new SystemException("Given image is not a valid Imagick-object.");
		}
		
		$image->writeImage($filename);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::getHeight()
	 */
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::getWidth()
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::isSupported()
	 */
	public static function isSupported() {
		return class_exists('\Imagick', false);
	}
}
