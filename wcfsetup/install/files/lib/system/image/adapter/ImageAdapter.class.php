<?php
namespace wcf\system\image\adapter;
use wcf\system\exception\SystemException;

/**
 * Wrapper for image adapters.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.image.adapter
 * @category 	Community Framework
 */
class ImageAdapter implements IImageAdapter {
	/**
	 * IImageAdapter object
	 * @var	IImageAdapter
	 */
	protected $adapter = null;
	
	/**
	 * Creates a new ImageAdapter instance.
	 * 
	 * @param	string		$adapterClassName
	 */
	public function __construct($adapterClassName) {
		$this->adapter = new $adapterClassName();
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::load()
	 */
	public function load($image, $type = 0) {
		$this->adapter->load($image, $type);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::loadFile()
	 */
	public function loadFile($file) {
		if (!file_exists($file) || !is_readable($file)) {
			throw new SystemException("Image '".$file."' is not readable or does not exists.");
		}
		
		$this->adapter->loadFile($file);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::createThumbnail()
	 */
	public function createThumbnail($maxWidth, $maxHeight, $obtainDimensions = true) {
		if ($maxWidth > $this->getWidth() || $maxHeight > $this->getHeight()) {
			throw new SystemException("Dimensions for thumbnail can not exceed image dimensions.");
		}
		
		return $this->adapter->createThumbnail($maxWidth, $maxHeight, $obtainDimensions);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::clip()
	 */
	public function clip($originX, $originY, $width, $height) {
		// validate if coordinates and size are within bounds
		if ($originX < 0 || $originY < 0) {
			throw new SystemException("Clipping an image requires valid offsets, an offset below zero is invalid.");
		}
		if ($width <= 0 || $height <= 0) {
			throw new SystemException("Clipping an image requires valid dimensions, width or height below or equal zero are invalid.");
		}
		if ((($originX + $width) > $this->getWidth()) || (($originY + $height) > $this->getHeight())) {
			throw new SystemException("Offset and dimension can not exceed image dimensions.");
		}
		
		$this->adapter->clip($originX, $originY, $width, $height);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::resize()
	 */
	public function resize($originX, $originY, $originWidth, $originHeight, $targetX, $targetY, $targetWidth, $targetHeight) {
		// use origin dimensions if target dimensions are both zero
		if ($targetWidth == 0 && $targetHeight == 0) {
			$targetWidth = $originWidth;
			$targetHeight = $originHeight;
		}
		
		$this->adapter->resize($originX, $originY, $originWidth, $originHeight, $targetX, $targetY, $targetWidth, $targetHeight);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::drawRectangle()
	 */
	public function drawRectangle($startX, $startY, $endX, $endY) {
		if (!$this->adapter->hasColor()) {
			throw new SystemException("Cannot draw a rectangle unless a color has been specified with setColor().");
		}
		
		$this->adapter->drawRectangle($startX, $startY, $endX, $endY);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::drawText()
	 */
	public function drawText($string, $x, $y) {
		if (!$this->adapter->hasColor()) {
			throw new SystemException("Cannot draw text unless a color has been specified with setColor().");
		}
		
		$this->adapter->drawText($string, $x, $y);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::setColor()
	 */
	public function setColor($red, $green, $blue) {
		$this->adapter->setColor($red, $green, $blue);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::hasColor()
	 */
	public function hasColor() {
		return $this->adapter->hasColor();
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::writeImage()
	 */
	public function writeImage($image, $filename) {
		$this->adapter->writeImage($image, $filename);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::getImage()
	 */
	public function getImage() {
		return $this->adapter->getImage();
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::getWidth()
	 */
	public function getWidth() {
		return $this->adapter->getWidth();
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::getHeight()
	 */
	public function getHeight() {
		return $this->adapter->getHeight();
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::isSupported()
	 */
	public static function isSupported() {
		return false;
	}
}
