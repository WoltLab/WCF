<?php
class GDImageAdapter {
	/**
	 * image height
	 * @var	integer
	 */	
	protected $height = 0;
	
	/**
	 * loaded image
	 * @var	resource
	 */	
	protected $image = null;
	
	/**
	 * image type
	 * @var	integer
	 */
	protected $type = 0;
	
	/**
	 * image width
	 * @var	integer
	 */	
	protected $width = 0;
	
	/**
	 * Loads an image from a resource.
	 * 
	 * @param	resource	$image
	 * @param	integer		$type
	 */
	public function load($image, $type = '') {
		if (!is_resource($image)) {
			throw new SystemException("Image resource is invalid.");
		}
		
		if (empty($type)) {
			throw new SystemException("Image type is missing.");
		}
		
		$this->image = $image;
		$this->type = $type;
		
		$this->height = imageSY($this->image);
		$this->width = imageSX($this->image);
	}
	
	/**
	 * Loads an image from file.
	 * 
	 * @param	string		$file
	 */	
	public function loadFile($file) {
		list($this->width, $this->height, $this->type) = getImageSize($file);
		
		switch ($this->type) {
			case IMAGETYPE_GIF:
				$this->image = imageCreateFromGif($file);
			break;
			
			case IMAGETYPE_JPEG:
				$this->image = imageCreateFromJpeg($file);
			break;
			
			case IMAGETYPE_PNG:
				$this->image = imageCreateFromPng($file);
			break;
			
			default:
				throw new SystemException("Could not read image '".$file."', format is not recognized.");
			break;
		}
	}
	
	/**
	 * Creates a thumbnail from previously loaded image.
	 * 
	 * @param	integer		$maxWidth
	 * @param	integer		$maxHeight
	 * @param	boolean		$obtainDimensions
	 * @return	resource
	 */	
	public function createThumbnail($maxWidth, $maxHeight, $obtainDimensions = true) {
		if ($maxWidth > $this->width || $maxHeight > $this->height) {
			throw new SystemException("Dimensions for thumbnail can not exceed image dimensions.");
		}
		
		$width = $height = $x = $y = 0;
		
		if ($obtainDimensions) {
			$widthScale = $maxWidth / $this->width;
			$heightScale = $maxHeight / $this->height;
			
			if ($widthScale > $heightScale) {
				$width = round($this->width * $heightScale, 0);
				$height = $maxHeight;
			}
			else {
				$width = $maxWidth;
				$height = round($this->height * $widthScale, 0);
			}
		}
		else {
			if ($this->width > $this->height) {
				$x = ceil(($this->width - $this->height) / 2);
				$width = $height = $this->height;
			}
			else {
				$y = ceil(($this->height - $this->width) / 2);
				$height = $width = $this->width;
			}
		}
		
		// resize image
		$image = imageCreateTrueColor($width, $height);
		imageAlphaBlending($image, false);
		imageCopyResampled($image, $this->image, 0, 0, $x, $y, $width, $height, $this->width, $this->height);
		imageSaveAlpha($image, true);
		
		return $image;
	}
	
	/**
	 * Clips a part of currently loaded image, overwrites image resource within instance.
	 * 
	 * @param	integer		$originX
	 * @param	integer		$originY
	 * @param	integer		$width
	 * @param	integer		$height
	 * @see	wcf\system\image\adapter\GDImageAdapter::getImage()
	 */
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
		
		$image = imageCreateTrueColor($width, $height);
		imageAlphaBlending($image, false);
		
		imageCopy($image, $this->image, 0, 0, $originX, $originY, $width, $height);
		imageSaveAlpha($image, true);
		
		$this->image = $image;
	}
	
	/**
	 * Resizes an image with optional scaling, overwrites image resource within instance.
	 * 
	 * @param	integer		$originX
	 * @param	integer		$originY
	 * @param	integer		$originWidth
	 * @param	integer		$originHeight
	 * @param	integer		$targetX
	 * @param	integer		$targetY
	 * @param	integer		$targetWidth
	 * @param	integer		$targetHeight
	 * @see	wcf\system\image\adapter\GDImageAdapter::getImage()
	 */
	public function resize($originX, $originY, $originWidth, $originHeight, $targetX = 0, $targetY = 0, $targetWidth = 0, $targetHeight = 0) {
		// use origin dimensions if target dimensions are both zero
		if ($targetWidth == 0 && $targetHeight == 0) {
			$targetWidth = $originWidth;
			$targetHeight = $originHeight;
		}
		
		$image = imageCreateTrueColor($targetWidth, $targetHeight);
		imageAlphaBlending($image, false);
		
		imageCopyResampled($image, $this->image, $targetX, $targetY, $originX, $originY, $targetWidth, $targetHeight, $originWidth, $originHeight);
		imageSaveAlpha($image, true);
		
		$this->image = $image;
	}
	
	/**
	 * Draws a rectangle, overwrites image resource within instance.
	 * 
	 * @param	integer		$startX
	 * @param	integer		$startY
	 * @param	integer		$endX
	 * @param	integer		$endY
	 * @param	integer		$color
	 * @see	wcf\system\image\adapter\GDImageAdapter::getColor()
	 * @see	wcf\system\image\adapter\GDImageAdapter::getImage()
	 */
	public function drawRectangle($startX, $startY, $endX, $endY, $color) {
		imageFilledRectangle($this->image, $startX, $startY, $endX, $endY, $color);
	}
	
	/**
	 * Draws a line of text, overwrites image resource within instance.
	 * 
	 * @param	string		$string
	 * @param	integer		$x
	 * @param	integer		$y
	 * @param	integer		$color
	 * @param	integer		$font
	 * @see	wcf\system\image\adapter\GDImageAdapter::getColor()
	 * @see	wcf\system\image\adapter\GDImageAdapter::getImage()
	 */
	public function drawText($string, $x, $y, $color, $font = 3) {
		imageString($this->image, $font, $x, $y, $string, $color);
	}
	
	/**
	 * Creates a color value based upon RGB.
	 * 
	 * @param	integer		$red
	 * @param	integer		$green
	 * @param	integer		$blue
	 * @return	integer
	 */	
	public function getColor($red, $green, $blue) {
		return imageColorAllocate($this->image, $red, $green, $blue);
	}
	
	/**
	 * Writes an image to disk.
	 * 
	 * @param	resource	$image
	 * @param	string		$filename
	 */	
	public function writeImage($image, $filename) {
		ob_start();
		
		if ($this->type == IMAGETYPE_GIF && function_exists('imageGIF')) {
			imageGIF($image);
		}
		else if (($this->type == IMAGETYPE_GIF || $this->type == IMAGETYPE_PNG) && function_exists('imagePNG')) {
			imagePNG($image);
		}
		else if (function_exists('imageJPEG')) {
			imageJPEG($image, '', 90);
		}
		
		$thumbnail = ob_get_contents();
		ob_end_clean();
		
		file_put_contents($filename, $thumbnail);
	}
	
	/**
	 * Returns image resource.
	 * 
	 * @return	resource
	 */
	public function getImage() {
		return $this->image;
	}
}
