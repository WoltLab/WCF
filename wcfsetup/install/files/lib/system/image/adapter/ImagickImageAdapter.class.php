<?php
require_once('GD.php');

class ImagickImageAdapter extends GDImageAdapter {
	protected $imagick = null;
	protected $color = null;
	
	
	public function __construct() {
		$this->imagick = new \Imagick();
	}
	
	public function load($image, $type = '') {
		if (!($image instanceof \Imagick)) {
			throw new SystemException("Object must be an instance of Imagick");
		}
		
		$this->imagick = $imagick;
		$this->height = $this->imagick->getImageHeight();
		$this->width = $this->imagick->getImageWidth();
	}
	
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
	
	public function createThumbnail($maxWidth, $maxHeight, $obtainDimensions = true) {
		$thumbnail = $this->imagick;
		
		$thumbnail = $this->imagick;
		$thumbnail->cropThumbnailImage($maxWidth, $maxHeight);
		
		return $thumbnail;
	}
	
	public function clip($originX, $originY, $width, $height) {
		// validate if coordinates and size are within bounds
		if ($originX < 0 || $originY < 0) {
			throw new SystemException("Clipping an image requires valid offsets, an offset below zero is invalid.");
		}
		if ($width <= 0 || $height <= 0) {
			throw new SystemException("Clipping an image requires valid dimensions, width or height below or equal zero are invalid.");
		}
		if ((($originX + $width) > $this->width) || (($originY + $height) > $this->height)) {
			throw new SystemException("Offset and dimension can not exceed image dimensions.");
		}
		
		$this->imagick->cropImage($width, $height, $originX, $originY);
	}
	
	public function drawRectangle($startX, $startY, $endX, $endY, $color) {
		$draw = new \ImagickDraw();
		$draw->setFillColor($this->color);
		$draw->setStrokeColor($this->color);
		$draw->rectangle($startX, $startY, $endX, $endY);
		
		$this->imagick->drawImage($draw);
	}
	
	public function drawText($string, $x, $y, $color, $font = 4) {
		$draw = new \ImagickDraw();
		$draw->setFillColor($this->color);
		$draw->setTextAntialias(true);
		
		// draw text
		$draw->annotation($x, $y, $string);
		$this->imagick->drawImage($draw);
	}
	
	public function setColor($red, $green, $blue) {
		$this->color = new \ImagickPixel();
		$this->color->setColor('rgb('.$red.','.$green.','.$blue.')');
		
	}
	
	public function hasColor() {
		if ($this->color instanceof \ImagickPixel) {
			return true;
		}
		
		return false;
	}
	
	public function getImage() {
		return $this->imagick;
	}
	
	public function writeImage($image, $filename) {
		$image->writeImage($filename);
	}
}
