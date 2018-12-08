<?php
namespace wcf\data;

/**
 * Every database object representing a file should implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * @since	3.0
 * 
 * @property-read	string		$fileType	type of the physical attachment file
 * @property-read	integer		$isImage	is `1` if the file is an image, otherwise `0`
 * @property-read	integer		$width		width of the file if `$isImage` is `1`, otherwise `0`
 * @property-read	integer		$height		height of the file if `$isImage` is `1`, otherwise `0`
 */
interface IFile extends IStorableObject {
	/**
	 * Returns the physical location of the file.
	 * 
	 * @return	string
	 */
	public function getLocation();
}
