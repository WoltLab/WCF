<?php
namespace wcf\data\user\avatar;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a default avatar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Avatar
 */
class DefaultAvatar implements IUserAvatar {
	/**
	 * image size
	 * @var	integer
	 */
	public $size = UserAvatar::AVATAR_SIZE;
	
	/**
	 * @inheritDoc
	 */
	public function getURL($size = null) {
		return WCF::getPath().'images/avatars/avatar-default.svg';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getImageTag($size = null) {
		if ($size === null) $size = $this->size;
		
		return '<img src="'.StringUtil::encodeHTML($this->getURL($size)).'" width="'.$size.'" height="'.$size.'" alt="" class="userAvatarImage">';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getWidth() {
		return $this->size;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHeight() {
		return $this->size;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canCrop() {
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getCropImageTag($size = null) {
		return '';
	}
}
