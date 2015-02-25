<?php
namespace wcf\data\user\avatar;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a default avatar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.avatar
 * @category	Community Framework
 */
class DefaultAvatar implements IUserAvatar {
	/**
	 * image size
	 * @var	integer
	 */
	public $size = 150;
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::getURL()
	 */
	public function getURL($size = null) {
		if ($size === null) $size = $this->size;
		
		return WCF::getPath().'images/avatars/avatar-default.svg';
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::getImageTag()
	 */
	public function getImageTag($size = null) {
		if ($size === null) $size = $this->size;
		
		return '<img src="'.StringUtil::encodeHTML($this->getURL($size)).'" style="width: '.$size.'px; height: '.$size.'px" alt="'.WCF::getLanguage()->get('wcf.user.avatar.alt').'" class="userAvatarImage" />';
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::getWidth()
	 */
	public function getWidth() {
		return $this->size;
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::getHeight()
	 */
	public function getHeight() {
		return $this->size;
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::canCrop()
	 */
	public function canCrop() {
		return false;
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::getCropImageTag()
	 */
	public function getCropImageTag($size = null) {
		return '';
	}
}
