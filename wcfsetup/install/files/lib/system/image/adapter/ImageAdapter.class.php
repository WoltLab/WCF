<?php
namespace wcf\system\image\adapter;
use wcf\system\session\SystemException;

class ImageAdapter {
	protected $adapter = null;
	
	public function __construct($adapterClassName) {
		$this->adapter = new $adapterClassName();
	}
	
	public function load($image, $type = 0) {
		$this->adapter->load($image, $type);
	}
	
	public function loadFile($file) {
		if (!file_exists($file) || !is_readable($file)) {
			throw new SystemException("Image '".$file."' is not readable or does not exists.");
		}
		
		$this->adapter->loadFile($file);
	}
	
	public function createThumbnail($maxWidth, $maxHeight, $obtainDimensions = true) {
		if ($maxWidth > $this->getWidth() || $maxHeight > $this->getHeight()) {
			throw new SystemException("Dimensions for thumbnail can not exceed image dimensions.");
		}
		
		return $this->adapter->createThumbnail($maxWidth, $maxHeight, $obtainDimensions);
	}
	
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
	
	public function resize($originX, $originY, $originWidth, $originHeight, $targetX, $targetY, $targetWidth, $targetHeight) {
		// use origin dimensions if target dimensions are both zero
		if ($targetWidth == 0 && $targetHeight == 0) {
			$targetWidth = $originWidth;
			$targetHeight = $originHeight;
		}
		
		$this->adapter->resize($originX, $originY, $originWidth, $originHeight, $targetX, $targetY, $targetWidth, $targetHeight);
	}
	
	public function drawRectangle($startX, $startY, $endX, $endY) {
		if (!$this->adapter->hasColor()) {
			throw new SystemException("Cannot draw a rectangle unless a color has been specified with setColor().");
		}
		
		$this->adapter->drawRectangle($startX, $startY, $endX, $endY);
	}
	
	public function drawText($string, $x, $y) {
		if (!$this->adapter->hasColor()) {
			throw new SystemException("Cannot draw text unless a color has been specified with setColor().");
		}
		
		$this->adapter->drawText($string, $x, $y);
	}
	
	public function setColor($red, $green, $blue) {
		$this->adapter->setColor($red, $green, $blue);
	}
	
	public function getWidth() {
		return $this->adapter->getWidth();
	}
	
	public function getHeight() {
		return $this->adapter->getHeight();
	}
}
