<?php
namespace wcf\data\user\avatar;
use wcf\data\DatabaseObject;
use wcf\util\StringUtil;
use wcf\system\WCF;

/**
 * Represents a user's avatar.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
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
	public static $avatarThumbnailSizes = array(32, 96, 128);
	
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
	 * minimum height and width of an uploaded avatar
	 * @var	integer
	 */
	const MIN_AVATAR_SIZE = 96;
	
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
		switch ($size) {
			case 16:
			case 24:
				$size = 32;
			break;
			
			case 48:
			case 64:
				if ($this->width > 96 || $this->height > 96) {
					$size = 96;
				}
				else {
					$size = null;
				}
			break;
		}
		
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
		
		$retinaSize = null;
		switch ($size) {
			case 16:
				$retinaSize = 32;
			break;
			
			case 24:
			case 32:
			case 48:
				$retinaSize = 96;
			break;
			
			case 64:
			case 96:
				if ($this->width >= 128 && $this->height >= 128) {
					$retinaSize = 128;
				}
			break;
		}
		
		return '<img src="'.StringUtil::encodeHTML($this->getURL($size)).'" '.($retinaSize !== null ? ('srcset="'.StringUtil::encodeHTML($this->getURL($retinaSize)).' 2x" ') : '').'style="width: '.$width.'px; height: '.$height.'px" alt="" class="userAvatarImage" />';
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::getCropImageTag()
	 */
	public function getCropImageTag($size = null) {
		$imageTag = $this->getImageTag($size);
		
		// append CSS classes and append title
		$title = StringUtil::encodeHTML(WCF::getLanguage()->get('wcf.user.avatar.type.custom.crop'));
		
		return str_replace('class="userAvatarImage"', 'class="userAvatarImage userAvatarCrop jsTooltip" title="'.$title.'"', $imageTag);
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
