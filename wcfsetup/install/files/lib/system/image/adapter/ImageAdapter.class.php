<?php
namespace wcf\system\image\adapter;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;

/**
 * Wrapper for image adapters.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Image\Adapter
 */
class ImageAdapter implements IImageAdapter, IMemoryAwareImageAdapter {
	/**
	 * IImageAdapter object
	 * @var	IImageAdapter
	 */
	protected $adapter = null;
	
	/**
	 * supported relative positions
	 * @var	string[]
	 */
	protected $relativePositions = [
		'topLeft',
		'topCenter',
		'topRight',
		'middleLeft',
		'middleCenter',
		'middleRight',
		'bottomLeft',
		'bottomCenter',
		'bottomRight'
	];
	
	/**
	 * Creates a new ImageAdapter instance.
	 * 
	 * @param	string		$adapterClassName
	 */
	public function __construct($adapterClassName) {
		$this->adapter = new $adapterClassName();
	}
	
	/**
	 * @inheritDoc
	 */
	public function load($image, $type = 0) {
		$this->adapter->load($image, $type);
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadFile($file) {
		if (!file_exists($file) || !is_readable($file)) {
			throw new SystemException("Image '".$file."' is not readable or does not exists.");
		}
		
		$this->adapter->loadFile($file);
	}
	
	/**
	 * @inheritDoc
	 */
	public function createEmptyImage($width, $height) {
		$this->adapter->createEmptyImage($width, $height);
	}
	
	/**
	 * @inheritDoc
	 */
	public function createThumbnail($maxWidth, $maxHeight, $preserveAspectRatio = true) {
		if ($maxWidth > $this->getWidth() && $maxHeight > $this->getHeight()) {
			throw new SystemException("Dimensions for thumbnail can not exceed image dimensions.");
		}
		
		$maxHeight = min($maxHeight, $this->getHeight());
		$maxWidth = min($maxWidth, $this->getWidth());
		
		return $this->adapter->createThumbnail($maxWidth, $maxHeight, $preserveAspectRatio);
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function resize($originX, $originY, $originWidth, $originHeight, $targetWidth, $targetHeight) {
		// use origin dimensions if target dimensions are both zero
		if ($targetWidth == 0 && $targetHeight == 0) {
			$targetWidth = $originWidth;
			$targetHeight = $originHeight;
		}
		
		$this->adapter->resize($originX, $originY, $originWidth, $originHeight, $targetWidth, $targetHeight);
	}
	
	/**
	 * @inheritDoc
	 */
	public function drawRectangle($startX, $startY, $endX, $endY) {
		if (!$this->adapter->hasColor()) {
			throw new SystemException("Cannot draw a rectangle unless a color has been specified with setColor().");
		}
		
		$this->adapter->drawRectangle($startX, $startY, $endX, $endY);
	}
	
	/**
	 * @inheritDoc
	 */
	public function drawText($text, $x, $y, $font, $size, $opacity = 1.0) {
		if (!$this->adapter->hasColor()) {
			throw new SystemException("Cannot draw text unless a color has been specified with setColor().");
		}
		
		// validate opacity
		if ($opacity < 0 || $opacity > 1) {
			throw new SystemException("Invalid opacity value given.");
		}
		
		$this->adapter->drawText($text, $x, $y, $font, $size, $opacity);
	}
	
	/**
	 * @inheritDoc
	 */
	public function drawTextRelative($text, $position, $margin, $offsetX, $offsetY, $font, $size, $opacity = 1.0) {
		if (!$this->adapter->hasColor()) {
			throw new SystemException("Cannot draw text unless a color has been specified with setColor().");
		}
		
		// validate position
		if (!in_array($position, $this->relativePositions)) {
			throw new SystemException("Unknown relative position '".$position."'.");
		}
		
		// validate margin
		if ($margin < 0 || $margin >= $this->getHeight() / 2 || $margin >= $this->getWidth() / 2) {
			throw new SystemException("Margin has to be positive and respect image dimensions.");
		}
		
		// validate opacity
		if ($opacity < 0 || $opacity > 1) {
			throw new SystemException("Invalid opacity value given.");
		}
		
		$this->adapter->drawTextRelative($text, $position, $margin, $offsetX, $offsetY, $font, $size, $opacity);
	}
	
	/**
	 * @inheritDoc
	 */
	public function textFitsImage($text, $margin, $font, $size) {
		return $this->adapter->textFitsImage($text, $margin, $font, $size);
	}
	
	/**
	 * @inheritDoc
	 */
	public function adjustFontSize($text, $margin, $font, $size) {
		// adjust font size
		while ($size && !$this->textFitsImage($text, $margin, $font, $size)) {
			$size--;
		}
		
		return $size;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setColor($red, $green, $blue) {
		$this->adapter->setColor($red, $green, $blue);
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasColor() {
		return $this->adapter->hasColor();
	}
	
	/**
	 * @inheritDoc
	 */
	public function setTransparentColor($red, $green, $blue) {
		$this->adapter->setTransparentColor($red, $green, $blue);
	}
	
	/**
	 * @inheritDoc
	 */
	public function writeImage($image, $filename = null) {
		if ($filename === null) {
			$filename = $image;
			$image = $this->adapter->getImage();
		}
		
		$this->adapter->writeImage($image, $filename);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getImage() {
		return $this->adapter->getImage();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getWidth() {
		return $this->adapter->getWidth();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHeight() {
		return $this->adapter->getHeight();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getType() {
		return $this->adapter->getType();
	}
	
	/**
	 * @inheritDoc
	 */
	public function rotate($degrees) {
		if ($degrees > 360.0 || $degrees < 0.0) {
			throw new SystemException("Degrees must be a value between 0 and 360.");
		}
		
		return $this->adapter->rotate($degrees);
	}
	
	/**
	 * @inheritDoc
	 */
	public function overlayImage($file, $x, $y, $opacity) {
		// validate file
		if (!file_exists($file)) {
			throw new SystemException("Image '".$file."' does not exist.");
		}
		
		// validate opacity
		if ($opacity < 0 || $opacity > 1) {
			throw new SystemException("Invalid opacity value given.");
		}
		
		$this->adapter->overlayImage($file, $x, $y, $opacity);
	}
	
	/**
	 * @inheritDoc
	 */
	public function overlayImageRelative($file, $position, $margin, $opacity) {
		// validate file
		if (!file_exists($file)) {
			throw new SystemException("Image '".$file."' does not exist.");
		}
		
		// validate position
		if (!in_array($position, $this->relativePositions)) {
			throw new SystemException("Unknown relative position '".$position."'.");
		}
		
		// validate margin
		if ($margin < 0 || $margin >= $this->getHeight() / 2 || $margin >= $this->getWidth() / 2) {
			throw new SystemException("Margin has to be positive and respect image dimensions.");
		}
		
		// validate opacity
		if ($opacity < 0 || $opacity > 1) {
			throw new SystemException("Invalid opacity value given.");
		}
		
		$adapterClassName = get_class($this->adapter);
		
		/** @var IImageAdapter $overlayImage */
		$overlayImage = new $adapterClassName();
		$overlayImage->loadFile($file);
		$overlayHeight = $overlayImage->getHeight();
		$overlayWidth = $overlayImage->getWidth();
		
		// calculate y coordinate
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
				$x = floor(($this->getWidth() - $overlayWidth) / 2);
			break;
			
			case 'topRight':
			case 'middleRight':
			case 'bottomRight':
				$x = $this->getWidth() - $overlayWidth - $margin;
			break;
		}
		
		// calculate y coordinate
		$y = 0;
		switch ($position) {
			case 'topLeft':
			case 'topCenter':
			case 'topRight':
				$y = $margin;
			break;
			
			case 'middleLeft':
			case 'middleCenter':
			case 'middleRight':
				$y = floor(($this->getHeight() - $overlayHeight) / 2);
			break;
			
			case 'bottomLeft':
			case 'bottomCenter':
			case 'bottomRight':
				$y = $this->getHeight() - $overlayHeight - $margin;
			break;
		}
		
		$this->overlayImage($file, $x, $y, $opacity);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkMemoryLimit($width, $height, $mimeType) {
		if ($this->adapter instanceof IMemoryAwareImageAdapter) {
			return $this->adapter->checkMemoryLimit($width, $height, $mimeType);
		}
		
		$channels = $mimeType == 'image/png' ? 4 : 3;
		
		return FileUtil::checkMemoryLimit($width * $height * $channels * 2.1);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function isSupported() {
		return false;
	}
}
