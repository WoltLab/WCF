<?php
namespace wcf\system\image\adapter;
use wcf\system\exception\SystemException;

/**
 * Image adapter for bundled GD imaging library.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.image.adapter
 * @category 	Community Framework
 */
class GDImageAdapter implements IImageAdapter {
	/**
	 * active color
	 */	
	protected $color = null;
	
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
	 * @see	wcf\system\image\adapter\IImageAdapter::load()
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
	 * @see	wcf\system\image\adapter\IImageAdapter::loadFile()
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
	 * @see	wcf\system\image\adapter\IImageAdapter::createThumbnail()
	 */	
	public function createThumbnail($maxWidth, $maxHeight, $obtainDimensions = true) {
		$width = $height = $x = $y = 0;
		
		if ($obtainDimensions) {
			if ($maxWidth / $this->width < $maxHeight / $this->height) {
				$width = $maxWidth;
				$height = round($this->height * ($width / $this->width));
			}
			else {
				$height = $maxHeight;
				$width = round($this->width * ($height / $this->height));
			}
			
		}
		else {
			$width = $height = $maxWidth;
			
			if ($width > $height) {
				$x = ceil(($width - $height) / 2);
				$width = $height;
			}
			else {
				$y = ceil(($height - $width) / 2);
				$height = $width;
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
	 * @see	wcf\system\image\adapter\IImageAdapter::clip()
	 */
	public function clip($originX, $originY, $width, $height) {
		$image = imageCreateTrueColor($width, $height);
		imageAlphaBlending($image, false);
		
		imageCopy($image, $this->image, 0, 0, $originX, $originY, $width, $height);
		imageSaveAlpha($image, true);
		
		$this->image = $image;
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::resize()
	 */
	public function resize($originX, $originY, $originWidth, $originHeight, $targetX = 0, $targetY = 0, $targetWidth = 0, $targetHeight = 0) {
		$image = imageCreateTrueColor($targetWidth, $targetHeight);
		imageAlphaBlending($image, false);
		
		imageCopyResampled($image, $this->image, $targetX, $targetY, $originX, $originY, $targetWidth, $targetHeight, $originWidth, $originHeight);
		imageSaveAlpha($image, true);
		
		$this->image = $image;
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::drawRectangle()
	 */
	public function drawRectangle($startX, $startY, $endX, $endY) {
		imageFilledRectangle($this->image, $startX, $startY, $endX, $endY, $this->color);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::drawText()
	 */
	public function drawText($string, $x, $y) {
		imageString($this->image, 3, $x, $y, $string, $this->color);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::setColor()
	 */	
	public function setColor($red, $green, $blue) {
		$this->color = imageColorAllocate($this->image, $red, $green, $blue);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::hasColor()
	 */
	public function hasColor() {
		return ($this->color !== null);
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::writeImage()
	 */
	public function writeImage($image, $filename) {
		if (!is_resource($image)) {
			throw new SystemException("Given image is not a valid image resource.");
		}
		
		ob_start();
		
		if ($this->type == IMAGETYPE_GIF) {
			imageGIF($image);
		}
		else if ($this->type == IMAGETYPE_PNG) {
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
	 * @see	wcf\system\image\adapter\IImageAdapter::getWidth()
	 */	
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::getHeight()
	 */
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::getImage()
	 */
	public function getImage() {
		return $this->image;
	}
	
	/**
	 * @see	wcf\system\image\adapter\IImageAdapter::isSupported()
	 */	
	public static function isSupported() {
		return true;
	}
}
