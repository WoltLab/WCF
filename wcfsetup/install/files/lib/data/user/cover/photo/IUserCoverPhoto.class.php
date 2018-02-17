<?php
namespace wcf\data\user\cover\photo;

/**
 * Any displayable cover photo type should implement this class.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Cover\Photo
 */
interface IUserCoverPhoto {
	/**
	 * Deletes this cover photo.
	 */
	public function delete();
	
	/**
	 * Returns the physical location of this cover photo.
	 *
	 * @return	string
	 */
	public function getLocation();
	
	/**
	 * Returns the url to this cover photo.
	 *
	 * @return	string
	 */
	public function getURL();
	
	/**
	 * Returns the file name of this cover photo.
	 *
	 * @return	string
	 */
	public function getFilename();
}
