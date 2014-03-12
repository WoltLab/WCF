<?php
namespace wcf\data\user\avatar;
use wcf\data\DatabaseObject;
use wcf\util\StringUtil;
use wcf\system\WCF;

/**
 * Represents a user's avatar.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.avatar
 * @category	Community Framework
 */
class UserAvatar extends DatabaseObject implements IUserAvatar {
	/**
	 * needed avatar thumbnail sizes
	 * @var	array<integer>
	 */
	public static $avatarThumbnailSizes = array(16, 24, 32, 48, 96, 128);
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'user_avatar';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'avatarID';
	
	/**
	 * maximum thumbnail size
	 * @var	integer
	 */
	public static $maxThumbnailSize = 128;
	
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
		return substr($this->fileHash, 0, 2) . '/' . ($this->avatarID) . '-' . $this->fileHash . ($size !== null ? ('-' . $size) : '') . '.' . $this->avatarExtension;
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::getURL()
	 */
	public function getURL($size = null) {
		if ($size !== null && $size !== 'resized') {
			if ($size >= $this->width || $size >= $this->height) $size = null;
		}
		
		return WCF::getPath() . 'images/avatars/' . $this->getFilename($size);
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::getImageTag()
	 */
	public function getImageTag($size = null) {
		$width = $this->width;
		$height = $this->height;
		if ($size !== null) {
			if ($this->width > $size && $this->height > $size) {
				$width = $height = $size;
			}
			else if ($this->width > $size || $this->height > $size) {
				$widthFactor = $size / $this->width;
				$heightFactor = $size / $this->height;
				
				if ($widthFactor < $heightFactor) {
					$width = $size;
					$height = round($this->height * $widthFactor, 0);
				}
				else {
					$width = round($this->width * $heightFactor, 0);
					$height = $size;
				}
			}
		}
		
		return '<img src="'.StringUtil::encodeHTML($this->getURL($size)).'" style="width: '.$width.'px; height: '.$height.'px" alt="'.WCF::getLanguage()->get('wcf.user.avatar.alt').'" />';
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::getWidth()
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::getHeight()
	 */
	public function getHeight() {
		return $this->height;
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::canCrop()
	 */
	public function canCrop() {
		return $this->width != $this->height && $this->width > self::$maxThumbnailSize && $this->height > self::$maxThumbnailSize;
	}
}
