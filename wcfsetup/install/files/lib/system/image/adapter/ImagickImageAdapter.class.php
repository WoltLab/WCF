<?php
namespace wcf\system\image\adapter;
use wcf\system\exception\SystemException;

/**
 * Image adapter for ImageMagick imaging library.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.image.adapter
 * @category	Community Framework
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
	 * is true if the used configuration can write animated GIF files if the
	 * PHP Imagick API version is 3.1.0 RC 1
	 * @var	boolean
	 */
	protected $supportsWritingAnimatedGIF = true;
	
	/**
	 * Creates a new ImagickImageAdapter.
	 */
	public function __construct() {
		$this->imagick = new \Imagick();
		
		// check if writing animated gifs is supported
		$version = $this->imagick->getVersion();
		$versionNumber = preg_match('~([0-9]+\.[0-9]+\.[0-9]+)~', $version['versionString'], $match);
		if (version_compare($match[0], '6.3.6') < 0) {
			$this->supportsWritingAnimatedGIF = false;
		}
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::load()
	 */
	public function load($image, $type = '') {
		if (!($image instanceof \Imagick)) {
			throw new SystemException("Object must be an instance of Imagick");
		}
		
		$this->imagick = $image;
		
		$this->readImageDimensions();
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::loadFile()
	 */
	public function loadFile($file) {
		try {
			$this->imagick->readImage($file);
		}
		catch (\ImagickException $e) {
			throw new SystemException("Image '".$file."' is not readable or does not exist.");
		}
		
		$this->readImageDimensions();
	}
	
	/**
	 * Reads width and height of the image.
	 */
	protected function readImageDimensions() {
		// fix height/width for animated gifs as getImageHeight/getImageWidth
		// returns the height/width of ONE frame of the animated image,
		// not the "real" height/width of the image
		if ($this->imagick->getImageFormat() == 'GIF') {
			$imagick = $this->imagick->coalesceImages();
			
			$this->height = $imagick->getImageHeight();
			$this->width = $imagick->getImageWidth();
			
			$imagick->clear();
		}
		else {
			$this->height = $this->imagick->getImageHeight();
			$this->width = $this->imagick->getImageWidth();
		}
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::createEmptyImage()
	 */
	public function createEmptyImage($width, $height) {
		$this->imagick->newImage($width, $height, 'white');
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::createThumbnail()
	 */
	public function createThumbnail($maxWidth, $maxHeight, $obtainDimensions = true) {
		$thumbnail = clone $this->imagick;
		
		if ($thumbnail->getImageFormat() == 'GIF') {
			$thumbnail = $thumbnail->coalesceImages();
			
			do {
				if ($obtainDimensions) {
					$thumbnail->thumbnailImage($maxWidth, $maxHeight, true);
				}
				else {
					$thumbnail->cropThumbnailImage($maxWidth, $maxHeight);
				}
				
				$thumbnail->setImagePage($maxWidth, $maxHeight, 0, 0); 
			}
			while ($thumbnail->nextImage());
		}
		else if ($obtainDimensions) {
			$thumbnail->thumbnailImage($maxWidth, $maxHeight, true);
		}
		else {
			$thumbnail->cropThumbnailImage($maxWidth, $maxHeight);
		}
		
		return $thumbnail;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::clip()
	 */
	public function clip($originX, $originY, $width, $height) {
		if ($this->imagick->getImageFormat() == 'GIF') {
			$this->imagick = $this->imagick->coalesceImages();
			
			do {
				$this->imagick->cropImage($width, $height, $originX, $originY);
				$this->imagick->setImagePage($width, $height, 0, 0);
			}
			while ($this->imagick->nextImage());
		}
		else {
			$this->imagick->cropImage($width, $height, $originX, $originY);
		}
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::resize()
	 */
	public function resize($originX, $originY, $originWidth, $originHeight, $targetWidth, $targetHeight) {
		$this->clip($originX, $originY, $originWidth, $originHeight);
		
		$this->imagick->resizeImage($targetWidth, $targetHeight, \Imagick::FILTER_POINT, 0);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::drawRectangle()
	 */
	public function drawRectangle($startX, $startY, $endX, $endY) {
		$draw = new \ImagickDraw();
		$draw->setFillColor($this->color);
		$draw->setStrokeColor($this->color);
		$draw->rectangle($startX, $startY, $endX, $endY);
		
		$this->imagick->drawImage($draw);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::drawText()
	 */
	public function drawText($string, $x, $y) {
		$draw = new \ImagickDraw();
		$draw->setFillColor($this->color);
		$draw->setTextAntialias(true);
		
		// draw text
		$draw->annotation($x, $y, $string);
		$this->imagick->drawImage($draw);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::setColor()
	 */
	public function setColor($red, $green, $blue) {
		$this->color = new \ImagickPixel();
		$this->color->setColor('rgb('.$red.','.$green.','.$blue.')');
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::hasColor()
	 */
	public function hasColor() {
		if ($this->color instanceof \ImagickPixel) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::setTransparentColor()
	 */
	public function setTransparentColor($red, $green, $blue) {
		$color = 'rgb(' . $red . ',' . $green . ',' . $blue . ')';
		$this->imagick->paintTransparentImage($color, 0.0, 0);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::getImage()
	 */
	public function getImage() {
		return $this->imagick;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::writeImage()
	 */
	public function writeImage($image, $filename) {
		if (!($image instanceof \Imagick)) {
			throw new SystemException("Given image is not a valid Imagick-object.");
		}
		
		// circumvent writeImages() bug in version 3.1.0 RC 1
		if (phpversion('imagick') == '3.1.0RC1' && $this->supportsWritingAnimatedGIF) {
			$file = fopen($filename, 'w');
			$image->writeImagesFile($file);
			fclose($file);
		}
		else {
			$image->writeImages($filename, true);
		}
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::getHeight()
	 */
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::getWidth()
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::getType()
	 */
	public function getType() {
		return 0;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::rotate()
	 */
	public function rotate($degrees) {
		$image = clone $this->imagick;
		$image->rotateImage(($this->color ?: new \ImagickPixel()), $degrees);
		
		return $image;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::isSupported()
	 */
	public static function isSupported() {
		return class_exists('\Imagick', false);
	}
}
