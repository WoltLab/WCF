<?php
namespace wcf\data;

/**
 * Every database object representing a file supporting thumbnails should implement
 * this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * @since	3.0
 */
interface IThumbnailFile extends IFile {
	/**
	 * Returns the link to the thumbnail file with the given size.
	 * 
	 * @param	string		$size
	 * @return	string
	 */
	public function getThumbnailLink($size);
	
	/**
	 * Returns the physical location of the thumbnail file with the given size.
	 * 
	 * @param	string		$size
	 * @return	string
	 */
	public function getThumbnailLocation($size);
	
	/**
	 * Returns the available thumbnail sizes.
	 * 
	 * @return	array
	 */
	public static function getThumbnailSizes();
}
