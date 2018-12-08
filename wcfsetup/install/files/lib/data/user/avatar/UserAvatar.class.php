<?php
namespace wcf\data\user\avatar;
use wcf\data\DatabaseObject;
use wcf\util\StringUtil;
use wcf\system\WCF;

/**
 * Represents a user's avatar.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Avatar
 * 
 * @property-read	integer		$avatarID		unique id of the user avatar
 * @property-read	string		$avatarName		name of the original avatar file
 * @property-read	string		$avatarExtension	extension of the avatar file
 * @property-read	integer		$width			width of the user avatar image
 * @property-read	integer		$height			height of the user avatar image
 * @property-read	integer|null	$userID			id of the user to which the user avatar belongs or null
 * @property-read	string		$fileHash		SHA1 hash of the original avatar file
 */
class UserAvatar extends DatabaseObject implements IUserAvatar {
	/**
	 * needed avatar thumbnail sizes
	 * @var	integer[]
	 * @deprecated 3.0
	 */
	public static $avatarThumbnailSizes = [32, 96, 128, 256];
	
	/**
	 * maximum thumbnail size
	 * @var	integer
	 * @deprecated 3.0
	 */
	public static $maxThumbnailSize = 128;
	
	/**
	 * minimum height and width of an uploaded avatar
	 * @var	integer
	 * @deprecated 3.0
	 */
	const MIN_AVATAR_SIZE = 96;
	
	/**
	 * minimum height and width of an uploaded avatar
	 * @var	integer
	 */
	const AVATAR_SIZE = 128;
	
	/**
	 * Returns the physical location of this avatar.
	 * 
	 * @param	integer		$size
	 * @return	string
	 */
	public function getLocation($size = null) {
		return WCF_DIR . 'images/avatars/' . $this->getFilename($size);
	}
	
	/**
	 * Returns the file name of this avatar.
	 * 
	 * @param	integer		$size
	 * @return	string
	 */
	public function getFilename($size = null) {
		return substr($this->fileHash, 0, 2) . '/' . $this->avatarID . '-' . $this->fileHash . ($size !== null ? ('-' . $size) : '') . '.' . $this->avatarExtension;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL($size = null) {
		return WCF::getPath() . 'images/avatars/' . $this->getFilename();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getImageTag($size = null) {
		return '<img src="'.StringUtil::encodeHTML($this->getURL($size)).'" width="'.$size.'" height="'.$size.'" alt="" class="userAvatarImage">';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCropImageTag($size = null) {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canCrop() {
		return false;
	}
}
