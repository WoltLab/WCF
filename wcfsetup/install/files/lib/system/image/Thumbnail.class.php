<?php
namespace wcf\system\image;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Generates a thumbnail of given source file image.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.image
 * @category 	Community Framework
 */
class Thumbnail {
	/**
	 * path to source file
	 *
	 * @var string
	 */
	protected $sourceFile = '';
	
	/**
	 * maximum image width
	 *
	 * @var integer
	 */
	protected $maxWidth = 0;
	
	/**
	 * maximum image height
	 *
	 * @var integer
	 */
	protected $maxHeight = 0;
	
	/**
	 * true, to show source information in thumbnail
	 *
	 * @var boolean
	 */
	protected $appendSourceInfo = false;
	
	/**
	 * true, to prefer embedded thumbnails
	 *
	 * @var boolean
	 */
	protected $useEmbedded = true;
	
	/**
	 * true, to generate quadratic thumbnails
	 * 
	 * @var	boolean
	 */
	protected $quadratic = false;
		
	/**
	 * mime type of the thumbnail
	 *
	 * @var string
	 */
	protected $mimeType = '';
	
	/**
	 * name of the source image
	 *
	 * @var string
	 */
	protected $sourceName = '';
	
	/**
	 * width of the source image
	 * 
	 * @var	integer
	 */
	protected $sourceWidth = 0;
	
	/**
	 * height of the source image
	 * 
	 * @var	integer
	 */
	protected $sourceHeight = 0;
	
	/**
	 * file size of the source image
	 * 
	 * @var	integer
	 */
	protected $sourceSize = 0;
	
	/**
	 * height of the source information
	 * 
	 * @var	integer
	 */
	protected static $sourceInfoLineHeight = 16;
	
	/**
	 * image type of the source image
	 * 
	 * @var	integer
	 */
	protected $imageType = 0;
	
	/**
	 * Creates a new Thumbnail object.
	 * 
	 * @param	string		$sourceFile
	 * @param	integer		$maxWidth
	 * @param	integer		$maxHeight
	 * @param	boolean		$appendSourceInfo
	 */
	public function __construct($sourceFile, $maxWidth = 100, $maxHeight = 100, $appendSourceInfo = false, $sourceName = null, $useEmbedded = true, $quadratic = false) {
		$this->sourceFile = $sourceFile;
		$this->maxWidth = $maxWidth;
		$this->maxHeight = $maxHeight;
		$this->appendSourceInfo = $appendSourceInfo;
		$this->useEmbedded = $useEmbedded;
		$this->quadratic = $quadratic;
		if ($this->appendSourceInfo) {
			// get source info
			if ($sourceName != null) $this->sourceName = $sourceName;
			else $this->sourceName = basename($this->sourceFile);
			list($this->sourceWidth, $this->sourceHeight, $type) = @getImageSize($this->sourceFile);
			$this->sourceSize = @filesize($sourceFile);
		}
	}
	
	/** 
	 * Creates a thumbnail picture (jpg/png) of a big image
	 * 
	 * @param	boolean 	$rescale
	 * @return	string		thumbnail 
	 */
	public function makeThumbnail($rescale = false) {
		list($width, $height, $this->imageType) = @getImageSize($this->sourceFile);
	
		// check image size
		if ($this->checkSize($width, $height, $rescale)) {
			return false;	
		}

		// try to extract the embedded thumbnail first (faster)
		$thumbnail = false;
		if (!$rescale && $this->useEmbedded) {
			$thumbnail = $this->extractEmbeddedThumbnail();
		}
		if (!$thumbnail) {
			// calculate uncompressed filesize
			// and cancel to avoid a memory_limit error
			$memoryLimit = ini_get('memory_limit');
			if ($memoryLimit != '') {
				$memoryLimit = substr($memoryLimit, 0, -1) * 1024 * 1024;
				$fileSize = $width * $height * ($this->imageType == 3 ? 4 : 3);
		
				if (($fileSize * 2.1) + memory_get_usage() > ($memoryLimit)) {
					return false;
				}
			}
			
			// calculate new picture size
			$x = $y = 0;
			if ($this->quadratic) {
				$newWidth = $newHeight = $this->maxWidth;
				if ($this->appendSourceInfo) $newHeight -= self::$sourceInfoLineHeight * 2;
				
				if ($width > $height) {
					$x = ceil(($width - $height) / 2);
					$width = $height;
				}
				else {
					$y = ceil(($height - $width) / 2);
					$height = $width;
				}
			}
			else {
				$maxHeight = $this->maxHeight;
				if ($this->appendSourceInfo) $maxHeight -= self::$sourceInfoLineHeight * 2;
				if ($this->maxWidth / $width < $maxHeight / $height) {
					$newWidth = $this->maxWidth;
					$newHeight = round($height * ($newWidth / $width));
				}
				else {
					$newHeight = $maxHeight;	
					$newWidth = round($width * ($newHeight / $height));
				}
			}
			
			// resize image
			$imageResource = false;
			
			// jpeg image
			if ($this->imageType == 2 && function_exists('imagecreatefromjpeg')) {
				$imageResource = @imageCreateFromJPEG($this->sourceFile);
			}
			// gif image
			if ($this->imageType == 1 && function_exists('imagecreatefromgif')) {
				$imageResource = @imageCreateFromGIF($this->sourceFile);
			}
			// png image
			if ($this->imageType == 3 && function_exists('imagecreatefrompng')) {
				$imageResource = @imageCreateFromPNG($this->sourceFile);
			}
			
			// could not create image
			if (!$imageResource) {
				return false;
			}
			
			// resize image
			if (function_exists('imageCreateTrueColor') && function_exists('imageCopyResampled')) {
				$imageNew = @imageCreateTrueColor($newWidth, $newHeight);
				imageAlphaBlending($imageNew, false);
				@imageCopyResampled($imageNew, $imageResource, 0, 0, $x, $y, $newWidth, $newHeight, $width, $height);
				imageSaveAlpha($imageNew, true);
			}
			else if (function_exists('imageCreate') && function_exists('imageCopyResized')) {
				$imageNew = @imageCreate($newWidth, $newHeight);
				imageAlphaBlending($imageNew, false);
				@imageCopyResized($imageNew, $imageResource, 0, 0, $x, $y, $newWidth, $newHeight, $width, $height);
				imageSaveAlpha($imageNew, true);
			}
			else return false;
			
			// create thumbnail
			ob_start();
			
			if ($this->imageType == 1 && function_exists('imageGIF')) {
				@imageGIF($imageNew);
				$this->mimeType = 'image/gif';
			}
			else if (($this->imageType == 1 || $this->imageType == 3) && function_exists('imagePNG')) {
				@imagePNG($imageNew);
				$this->mimeType = 'image/png';
			}
			else if (function_exists('imageJPEG')) {
				@imageJPEG($imageNew, '', 90);
				$this->mimeType = 'image/jpeg';
			}
			else {
				return false;
			}
			
			@imageDestroy($imageNew);
			$thumbnail = ob_get_contents();
			ob_end_clean();
		}
		
		if ($thumbnail && $this->appendSourceInfo && !$rescale) {
			$thumbnail = $this->appendSourceInfo($thumbnail);
		}
		
		return $thumbnail;
	}
	
	/**
	 * Appends information about the source image to the thumbnail.
	 * 
	 * @param	string		$thumbnail
	 * @return	string
	 */
	protected function appendSourceInfo($thumbnail) {
		if (!function_exists('imageCreateFromString') || !function_exists('imageCreateTrueColor')) {
			return $thumbnail;
		}
		
		$imageSrc = imageCreateFromString($thumbnail);
		
		// get image size
		$width = imageSX($imageSrc);
		$height = imageSY($imageSrc);
		
		// increase height
		$heightDst = $height + self::$sourceInfoLineHeight * 2;
		
		// create new image
		$imageDst = imageCreateTrueColor($width, $heightDst);
		imageAlphaBlending($imageDst, false);
		
		// set background color
		$background = imageColorAllocate($imageDst, 102, 102, 102);
		imageFill($imageDst, 0, 0, $background);

		// copy image
		imageCopy($imageDst, $imageSrc, 0, 0, 0, 0, $width, $height);
		imageSaveAlpha($imageDst, true);
		
		// get font size
		$font = 2;
		$fontWidth = imageFontWidth($font);
		$fontHeight = imageFontHeight($font);
		$fontColor = imageColorAllocate($imageDst, 255, 255, 255);
		
		// write source info
		$line1 = $this->sourceName;
		
		// imageString supports only ISO-8859-1 encoded strings
		$line1 = StringUtil::convertEncoding('UTF-8', 'ISO-8859-1', $line1);
		
		// truncate text if necessary
		$maxChars = floor($width / $fontWidth);
		if (strlen($line1) > $maxChars) {
			$line1 = $this->truncateSourceName($line1, $maxChars);
		}
		
		$line2 = $this->sourceWidth.'x'.$this->sourceHeight.' '.FileUtil::formatFilesize($this->sourceSize);
		
		// write line 1
		// calculate text position
		$textX = 0;
		$textY = 0;
		
		if ($fontHeight < self::$sourceInfoLineHeight) {
			$textY = intval(round((self::$sourceInfoLineHeight - $fontHeight) / 2));
		}
		if (strlen($line1) * $fontWidth < $width) {
			$textX = intval(round(($width - strlen($line1) * $fontWidth) / 2));
		}
		
		imageString($imageDst, $font, $textX, $height + $textY, $line1, $fontColor);
		
		// write line 2
		// calculate text position
		$textX = 0;
		$textY = 0;
		
		if ($fontHeight < self::$sourceInfoLineHeight) {
			$textY = self::$sourceInfoLineHeight + intval(round((self::$sourceInfoLineHeight - $fontHeight) / 2));
		}
		if (strlen($line2) * $fontWidth < $width) {
			$textX = intval(round(($width - strlen($line2) * $fontWidth) / 2));
		}
		
		imageString($imageDst, $font, $textX, $height + $textY, $line2, $fontColor);
		
		
		// output image
		ob_start();
		
		if ($this->imageType == 1 && function_exists('imageGIF')) {
			@imageGIF($imageDst);
			$this->mimeType = 'image/gif';
		}
		else if (($this->imageType == 1 || $this->imageType == 3) && function_exists('imagePNG')) {
			@imagePNG($imageDst);
			$this->mimeType = 'image/png';
		}
		else if (function_exists('imageJPEG')) {
			@imageJPEG($imageDst, '', 90);
			$this->mimeType = 'image/jpeg';
		}
		else {
			return false;
		}
		
		@imageDestroy($imageDst);
		$thumbnail = ob_get_contents();
		ob_end_clean();
		
		return $thumbnail;
	}
	
	
	/** 
	 * Extracts the embedded thumbnail picture of a jpeg or tiff image
	 * 
	 * @return	string		thumbnail 
	 */
	protected function extractEmbeddedThumbnail() {
		if (!function_exists('exif_thumbnail')) {
			return false;
		}

		$width = $height = $type = 0;
		$thumbnail = @exif_thumbnail($this->sourceFile, $width, $height, $type);
		if ($thumbnail && $type && $width && $height) {
			// resize the extracted thumbnail again if necessary
			// (normally the thumbnail size is set to 160px
			// which is recommended in EXIF >2.1 and DCF)
			$this->mimeType = image_type_to_mime_type($type);
			if (!$this->checkSize($width, $height)) {
				// get temporary file name
				$this->sourceFile = FileUtil::getTemporaryFilename('thumbnail_');
				
				// create tmp file
				$tmpFile = new File($this->sourceFile);
				$tmpFile->write($thumbnail);
				$tmpFile->close();
				unset($thumbnail, $tmpFile);
				
				// resize tmp file again
				return $this->makeThumbnail(true);
			}
			
			return $thumbnail;
		}
		
		return false;
	}
	
	/**
	 * Checks the size of an image.
	 */
	protected function checkSize($width, $height, $rescale = true) {
		$maxHeight = $this->maxHeight;
		if ($this->appendSourceInfo && $rescale) {
			$maxHeight -= self::$sourceInfoLineHeight * 2;
		}
		
		if ($width > $this->maxWidth || $height > $maxHeight) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns the mime type of the generated thumbnail.
	 * 
	 * @return	string
	 */
	public function getMimeType() {
		return $this->mimeType;
	}
	
	/**
	 * Truncates the given file name to needed length.
	 * 
	 * @param	string		$name
	 * @param	string		$maxChars
	 * @return	string
	 */
	protected static function truncateSourceName($name, $maxChars) {
		$extension = '';
		$lastPosition = strrpos($name, '.');
		if ($lastPosition !== null) {
			$extension = substr($name, $lastPosition);
			$name = substr($name, 0, $lastPosition);
			$maxChars -= strlen($extension);
		}
		
		return substr($name, 0, $maxChars - 3) . '...' . $extension;
	}
}
?>