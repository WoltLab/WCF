<?php
namespace wcf\util;
use wcf\system\exception\SystemException;
use wcf\system\image\ImageHandler;

/**
 * Contains image-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class ImageUtil {
	/**
	 * image extensions
	 * @var array
	 */
	protected static $imageExtensions = ['jpeg', 'jpg', 'png', 'gif'];
	
	/**
	 * Checks the content of an image for bad sections, e.g. the use of javascript
	 * and returns false if any bad stuff was found.
	 * 
	 * @param	string		$file
	 * @return	boolean
	 */
	public static function checkImageContent($file) {
		// get file content
		$content = file_get_contents($file);
		
		// remove some characters
		$content = strtolower(preg_replace('/[^a-z0-9<\(]+/i', '', $content));
		$content = str_replace('description', '', $content);
		
		// search for javascript
		if (strpos($content, 'script') !== false || strpos($content, 'javascript') !== false || strpos($content, 'expression(') !== false) return false;
		
		return true;
	}
	
	/**
	 * Checks whether a given file is a valid image.
	 *
	 * @param       string          $location
	 * @param       string|null     $filename
	 * @param       bool            $handleSvgAsValidImage  flag, whether a svg file is handled as image
	 * @return      bool
	 */
	public static function isImage($location, $filename = null, $handleSvgAsValidImage = false) {
		if ($filename === null) {
			$filename = basename($location);
		}
		
		if (@getimagesize($location) !== false) {
			$extension = pathinfo($filename, PATHINFO_EXTENSION);
			
			if (in_array($extension, self::$imageExtensions)) {
				return true;
			}
		}
		else if ($handleSvgAsValidImage) {
			if (in_array(FileUtil::getMimeType($location), ['image/svg', 'image/svg+xml']) && pathinfo($filename, PATHINFO_EXTENSION) === 'svg') {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Return the file extension for an image with the given mime type.
	 * 
	 * @param	string		$mimeType
	 * @return	string
	 * @see	http://www.php.net/manual/en/function.image-type-to-mime-type.php
	 */
	public static function getExtensionByMimeType($mimeType) {
		switch ($mimeType) {
			case 'image/gif':
				return 'gif';
			case 'image/jpeg':
				return 'jpg';
			case 'image/png':
				return 'png';
			case 'application/x-shockwave-flash':
				return 'swf';
			case 'image/psd':
				return 'psd';
			case 'image/bmp':
			case 'image/x-ms-bmp':
				return 'bmp';
			case 'image/tiff':
				return 'tiff';
			default:
				return '';
		}
	}
	
	/**
	 * Enforces dimensions for given image.
	 *
	 * @param	string		$filename
	 * @param       integer         $maxWidth
	 * @param       integer         $maxHeight
	 * @param	boolean		$obtainDimensions
	 * @return	string          new filename if file was changed, otherwise old filename
	 * @since       5.2
	 */
	public static function enforceDimensions($filename, $maxWidth, $maxHeight, $obtainDimensions = true) {
		$imageData = getimagesize($filename);
		if ($imageData[0] > $maxWidth || $imageData[1] > $maxHeight) {
			$adapter = ImageHandler::getInstance()->getAdapter();
			$adapter->loadFile($filename);
			$filename = FileUtil::getTemporaryFilename();
			$thumbnail = $adapter->createThumbnail($maxWidth, $maxHeight, $obtainDimensions);
			$adapter->writeImage($thumbnail, $filename);
		}
		
		return $filename;
	}
	
	/**
	 * Rotates the given image based on the orientation stored in the exif data.
	 *
	 * @param	string		$filename
	 * @return	string          new filename if file was changed, otherwise old filename
	 * @since       5.2
	 */
	public static function fixOrientation($filename) {
		try {
			$exifData = ExifUtil::getExifData($filename);
			if (!empty($exifData)) {
				$orientation = ExifUtil::getOrientation($exifData);
				if ($orientation != ExifUtil::ORIENTATION_ORIGINAL) {
					$adapter = ImageHandler::getInstance()->getAdapter();
					$adapter->loadFile($filename);
					
					$newImage = null;
					switch ($orientation) {
						case ExifUtil::ORIENTATION_180_ROTATE:
							$newImage = $adapter->rotate(180);
							break;
						
						case ExifUtil::ORIENTATION_90_ROTATE:
							$newImage = $adapter->rotate(90);
							break;
						
						case ExifUtil::ORIENTATION_270_ROTATE:
							$newImage = $adapter->rotate(270);
							break;
						
						case ExifUtil::ORIENTATION_HORIZONTAL_FLIP:
						case ExifUtil::ORIENTATION_VERTICAL_FLIP:
						case ExifUtil::ORIENTATION_VERTICAL_FLIP_270_ROTATE:
						case ExifUtil::ORIENTATION_HORIZONTAL_FLIP_270_ROTATE:
							// unsupported
							break;
					}
					
					if ($newImage !== null) {
						$adapter->load($newImage, $adapter->getType());
					}
					
					$newFilename = FileUtil::getTemporaryFilename();
					$adapter->writeImage($newFilename);
					$filename = $newFilename;
				}
			}
		}
		catch (SystemException $e) {}
		
		return $filename;
	}
	
	/**
	 * Forbid creation of ImageUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
