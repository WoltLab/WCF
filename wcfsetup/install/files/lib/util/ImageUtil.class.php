<?php
namespace wcf\util;

/**
 * Contains image-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Util
 */
final class ImageUtil {
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
