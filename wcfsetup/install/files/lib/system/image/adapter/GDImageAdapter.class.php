<?php
namespace wcf\system\image\adapter;
use wcf\system\exception\SystemException;
use wcf\util\StringUtil;

/**
 * Image adapter for bundled GD imaging library.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.image.adapter
 * @category	Community Framework
 */
class GDImageAdapter implements IImageAdapter {
	/**
	 * active color
	 * @var	integer
	 */
	protected $color = null;
	
	/**
	 * red, green, blue data of the active color
	 * @var	array
	 */
	protected $colorData = array();
	
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
	 * value of the font parameter of gd functions
	 * @var	integer
	 */
	const FONT = 3;
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::load()
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
	 * @see	\wcf\system\image\adapter\IImageAdapter::loadFile()
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
	 * @see	\wcf\system\image\adapter\IImageAdapter::createEmptyImage()
	 */
	public function createEmptyImage($width, $height) {
		$this->image = imageCreate($width, $height);
		$this->type = IMAGETYPE_PNG;
		$this->setColor(0xFF, 0xFF, 0xFF);
		$this->color = null;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::createThumbnail()
	 */
	public function createThumbnail($maxWidth, $maxHeight, $obtainDimensions = true) {
		$width = $height = $x = $y = 0;
		$sourceWidth = $this->width;
		$sourceHeight = $this->height;
		
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
			$width = $maxWidth;
			$height = $maxHeight;
			
			if ($maxWidth / $this->width < $maxHeight / $this->height) {
				$cut = (($sourceWidth * ($maxHeight / $this->height)) - $maxWidth) / ($maxHeight / $this->height);
				$x = ceil($cut / 2);
				$sourceWidth = $sourceWidth - $x * 2;
			}
			else {
				$cut = (($sourceHeight * ($maxWidth / $this->width)) - $maxHeight) / ($maxWidth / $this->width);
				$y = ceil($cut / 2);
				$sourceHeight = $sourceHeight - $y * 2;
			}
		}
		
		// resize image
		$image = imageCreateTrueColor($width, $height);
		imageAlphaBlending($image, false);
		imageCopyResampled($image, $this->image, 0, 0, $x, $y, $width, $height, $sourceWidth, $sourceHeight);
		imageSaveAlpha($image, true);
		
		return $image;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::clip()
	 */
	public function clip($originX, $originY, $width, $height) {
		$image = imageCreateTrueColor($width, $height);
		imageAlphaBlending($image, false);
		
		imageCopy($image, $this->image, 0, 0, $originX, $originY, $width, $height);
		imageSaveAlpha($image, true);
		
		// reload image to update image resource, width and height
		$this->load($image, $this->type);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::resize()
	 */
	public function resize($originX, $originY, $originWidth, $originHeight, $targetWidth = 0, $targetHeight = 0) {
		$image = imageCreateTrueColor($targetWidth, $targetHeight);
		imageAlphaBlending($image, false);
		
		imageCopyResampled($image, $this->image, 0, 0, $originX, $originY, $targetWidth, $targetHeight, $originWidth, $originHeight);
		imageSaveAlpha($image, true);
		
		// reload image to update image resource, width and height
		$this->load($image, $this->type);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::drawRectangle()
	 */
	public function drawRectangle($startX, $startY, $endX, $endY) {
		imageFilledRectangle($this->image, $startX, $startY, $endX, $endY, $this->color);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::drawText()
	 */
	public function drawText($string, $x, $y, $opacity) {
		if (!StringUtil::isUTF8($string)) {
			// GD is buggy with UTF-8
			throw new SystemException("Only UTF-8 encoded text can be written onto images");
		}
		
		// convert UTF-8 characters > 127 to their numeric representation, e.g. A -> &#65;
		// todo: $string = mb_encode_numericentity($string, array(0x0, 0xFFFF, 0, 0xFFF), 'UTF-8');
		
		// set opacity
		$color = imagecolorallocatealpha($this->image, $this->colorData['red'], $this->colorData['green'], $this->colorData['blue'], (1 - $opacity) * 127);
		
		imageString($this->image, self::FONT, $x, $y, $string, $color);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::drawTextRelative()
	 */
	public function drawTextRelative($text, $position, $margin, $opacity) {
		// split text into multiple lines to add each line separately
		$lines = explode('\n', StringUtil::unifyNewlines($text));
		
		$characterWidth = imagefontwidth(self::FONT);
		$lineHeight = imagefontheight(self::FONT);
		$textHeight = $lineHeight * count($lines);
		
		foreach ($lines as $key => $line) {
			$lineWidth = mb_strlen($line) * $characterWidth;
			
			// calculate x coordinate
			$x = 0;
			switch ($position) {
				case 'topLeft':
				case 'middleLeft':
				case 'bottomLeft':
					$x = $margin;
				break;
				
				case 'topCenter':
				case 'middleCenter':
				case 'bottomCenter':
					$x = floor(($this->getWidth() - $lineWidth) / 2);
				break;
				
				case 'topRight':
				case 'middleRight':
				case 'bottomRight':
					$x = $this->getWidth() - $lineWidth - $margin;
				break;
			}
			
			// calculate y coordinate
			$y = 0;
			switch ($position) {
				case 'topLeft':
				case 'topCenter':
				case 'topRight':
					$y = $margin + $key * $lineHeight;
				break;
				
				case 'middleLeft':
				case 'middleCenter':
				case 'middleRight':
					$y = floor(($this->getHeight() - $textHeight) / 2) + $key * $lineHeight;
				break;
				
				case 'bottomLeft':
				case 'bottomCenter':
				case 'bottomRight':
					$y = $this->getHeight() - $textHeight + $key * $lineHeight - $margin;
				break;
			}
			
			$this->drawText($line, $x, $y, $opacity);
		}
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::setColor()
	 */
	public function setColor($red, $green, $blue) {
		$this->color = imageColorAllocate($this->image, $red, $green, $blue);
		
		// save data of the color
		$this->colorData = array(
			'red' => $red,
			'green' => $green,
			'blue' => $blue
		);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::hasColor()
	 */
	public function hasColor() {
		return ($this->color !== null);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::setTransparentColor()
	 */
	public function setTransparentColor($red, $green, $blue) {
		if ($this->type == IMAGETYPE_PNG) {
			$color = imagecolorallocate($this->image, $red, $green, $blue);
			imageColorTransparent($this->image, $color);
		}
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::writeImage()
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
			imageJPEG($image, null, 90);
		}
		
		$stream = ob_get_contents();
		ob_end_clean();
		
		file_put_contents($filename, $stream);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::getWidth()
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::getHeight()
	 */
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::getType()
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::getImage()
	 */
	public function getImage() {
		return $this->image;
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::rotate()
	 */
	public function rotate($degrees) {
		// imagerotate interpretes degrees as counter-clockwise
		return imagerotate($this->image, (360.0 - $degrees), ($this->color ?: 0));
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::overlayImage()
	 */
	public function overlayImage($file, $x, $y, $opacity) {
		$overlayImage = new self();
		$overlayImage->loadFile($file);
		
		// fix PNG alpha channel handling
		// see http://php.net/manual/en/function.imagecopymerge.php#92787
		$cut = imagecreatetruecolor($overlayImage->getWidth(), $overlayImage->getHeight());
		imagecopy($cut, $this->image, 0, 0, $x, $y, $overlayImage->getWidth(), $overlayImage->getHeight());
		imagecopy($cut, $overlayImage->image, 0, 0, 0, 0, $overlayImage->getWidth(), $overlayImage->getHeight());
		imagecopymerge($this->image, $cut, $x, $y, 0, 0, $overlayImage->getWidth(), $overlayImage->getHeight(), $opacity * 100);
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::overlayImageRelative()
	 */
	public function overlayImageRelative($file, $position, $margin, $opacity) {
		// does nothing
	}
	
	/**
	 * @see	\wcf\system\image\adapter\IImageAdapter::isSupported()
	 */
	public static function isSupported() {
		return true;
	}
}
