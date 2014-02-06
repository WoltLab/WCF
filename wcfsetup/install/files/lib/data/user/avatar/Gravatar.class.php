<?php
namespace wcf\data\user\avatar;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\util\FileUtil;
use wcf\system\WCF;

/**
 * Represents a gravatar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.avatar
 * @category	Community Framework
 * @see		http://www.gravatar.com
 */
class Gravatar extends DefaultAvatar {
	/**
	 * gravatar base url
	 * @var	string
	 */
	const GRAVATAR_BASE = 'http://gravatar.com/avatar/%s?s=%d&r=g&d=%s';
	
	/**
	 * gravatar local cache location
	 * @var	string
	 */
	const GRAVATAR_CACHE_LOCATION = 'images/avatars/gravatars/%s-%s.png';
	
	/**
	 * gravatar expire time (days)
	 * @var	integer
	 */
	const GRAVATAR_CACHE_EXPIRE = 7;
	
	/**
	 * user id
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * gravatar e-mail address
	 * @var	string
	 */
	public $gravatar = '';
	
	/**
	 * urls of this gravatar
	 * @var	array<string>
	 */
	protected $url = array();
	
	/**
	 * Creates a new Gravatar object.
	 * 
	 * @param	integer		$userID
	 * @param	string		$gravatar
	 */
	public function __construct($userID, $gravatar) {
		$this->userID = $userID;
		$this->gravatar = $gravatar;
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::getURL()
	 */
	public function getURL($size = null) {
		if ($size === null) $size = $this->size;
		
		if (!isset($this->url[$size])) {
			// try to use cached gravatar
			$cachedFilename = sprintf(self::GRAVATAR_CACHE_LOCATION, md5(mb_strtolower($this->gravatar)), $size);
			if (file_exists(WCF_DIR.$cachedFilename) && filemtime(WCF_DIR.$cachedFilename) > (TIME_NOW - (self::GRAVATAR_CACHE_EXPIRE * 86400))) {
				$this->url[$size] = WCF::getPath().$cachedFilename;
			}
			else {
				$this->url[$size] = LinkHandler::getInstance()->getLink('GravatarDownload', array(
					'forceFrontend' => true
				), 'userID='.$this->userID.'&size='.$size);
			}
		}
		
		return $this->url[$size];
	}
	
	/**
	 * Checks a given email address for gravatar support.
	 * 
	 * @param	string		$email
	 * @return	boolean
	 */
	public static function test($email) {
		$gravatarURL = sprintf(self::GRAVATAR_BASE, md5(mb_strtolower($email)), 80, '404');
		try {
			$tmpFile = FileUtil::downloadFileFromHttp($gravatarURL, 'gravatar');
			@unlink($tmpFile);
			return true;
		}
		catch (SystemException $e) {
			return false;
		}
	}
	
	/**
	 * @see	\wcf\data\user\avatar\IUserAvatar::canCrop()
	 */
	public function canCrop() {
		return false;
	}
}
