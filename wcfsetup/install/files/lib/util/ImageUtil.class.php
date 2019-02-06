<?php
namespace wcf\util;

/**
 * Contains image-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class ImageUtil {
	/**
	 * image extensions
	 * @var array
	 */
	protected static $imageExtensions = array('jpeg', 'jpg', 'png', 'gif');
	
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
		if (strstr($content, 'script') || strstr($content, 'javascript') || strstr($content, 'expression(')) return false;
		
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
			if (!in_array(FileUtil::getMimeType($location), array('image/svg', 'image/svg+xml')) && pathinfo($filename, PATHINFO_EXTENSION) === 'svg') {
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
	 * Forbid creation of ImageUtil objects.
	 */
	private function __construct() {
		// does nothing
	}
}
