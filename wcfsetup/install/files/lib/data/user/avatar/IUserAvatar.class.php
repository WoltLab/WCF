<?php
namespace wcf\data\user\avatar;

/**
 * Any displayable avatar type should implement this class.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Avatar
 */
interface IUserAvatar {
	/**
	 * Returns true if this avatar can be cropped.
	 * 
	 * @return	boolean
	 */
	public function canCrop();
	
	/**
	 * Returns the url to this avatar.
	 * 
	 * @param	integer		$size
	 * @return	string
	 */
	public function getURL($size = null);
	
	/**
	 * Returns the html code to display this avatar.
	 * 
	 * @param	integer		$size
	 * @return	string
	 */
	public function getImageTag($size = null);
	
	/**
	 * Returns the image tag used for cropping.
	 * 
	 * @param	integer		$size
	 * @return	string
	 */
	public function getCropImageTag($size = null);
	
	/**
	 * Returns the width of this avatar.
	 * 
	 * @return	integer
	 */
	public function getWidth();
	
	/**
	 * Returns the height of this avatar.
	 * 
	 * @return	integer
	 */
	public function getHeight();
}
