<?php
namespace wcf\data;

/**
 * Every database object representing a file supporting thumbnails should implement
 * this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category	Community Framework
 * @since	2.2
 */
interface IThumbnailFile extends IFile {
	/**
	 * Returns the link to the thumbnail file with the given size.
	 * 
	 * @param	string		$size
	 * @return	sting
	 */
	public function getThumbnailLink($size);
	
	/**
	 * Returns the physical location of the thumbnail file with the given size.
	 * 
	 * @param	string		$size
	 * @return	sting
	 */
	public function getThumbnailLocation($size);
	
	/**
	 * Returns the available thumbnail sizes.
	 * 
	 * @return	array
	 */
	public static function getThumbnailSizes();
}
