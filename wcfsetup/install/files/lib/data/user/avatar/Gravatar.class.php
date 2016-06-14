<?php
namespace wcf\data\user\avatar;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Represents a gravatar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Avatar
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
	const GRAVATAR_CACHE_LOCATION = 'images/avatars/gravatars/%s-%s.%s';
	
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
	 * file extension of the gravatar image
	 * @var	string
	 */
	public $fileExtension = 'png';
	
	/**
	 * urls of this gravatar
	 * @var	string[]
	 */
	protected $url = [];
	
	/**
	 * Creates a new Gravatar object.
	 * 
	 * @param	integer		$userID
	 * @param	string		$gravatar
	 * @param	string		$fileExtension
	 */
	public function __construct($userID, $gravatar, $fileExtension = 'png') {
		$this->userID = $userID;
		$this->gravatar = $gravatar;
		$this->fileExtension = $fileExtension;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL($size = null) {
		if ($size === null) $size = $this->size;
		else {
			switch ($size) {
				case 16:
				case 24:
					$size = 32;
					break;
				case 48:
				case 64:
					$size = 96;
					break;
			}
		}
		
		if (!isset($this->url[$size])) {
			// try to use cached gravatar
			$cachedFilename = sprintf(self::GRAVATAR_CACHE_LOCATION, md5(mb_strtolower($this->gravatar)), $size, $this->fileExtension);
			if (file_exists(WCF_DIR.$cachedFilename) && filemtime(WCF_DIR.$cachedFilename) > (TIME_NOW - (self::GRAVATAR_CACHE_EXPIRE * 86400))) {
				$this->url[$size] = WCF::getPath().$cachedFilename;
			}
			else {
				$this->url[$size] = LinkHandler::getInstance()->getLink('GravatarDownload', [
					'forceFrontend' => true
				], 'userID='.$this->userID.'&size='.$size);
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
		$gravatarURL = sprintf(self::GRAVATAR_BASE, md5(mb_strtolower($email)), 80, GRAVATAR_DEFAULT_TYPE);
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
	 * @inheritDoc
	 */
	public function getImageTag($size = null) {
		if ($size === null) $size = $this->size;
		
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
			case 96:
				$retinaSize = 128;
				break;
		}
		
		return '<img src="'.StringUtil::encodeHTML($this->getURL($size)).'" '.($retinaSize !== null ? ('srcset="'.StringUtil::encodeHTML($this->getURL($retinaSize)).' 2x" ') : '').'style="width: '.$size.'px; height: '.$size.'px" alt="" class="userAvatarImage">';
	}
	
	/**
	 * @inheritDoc
	 */
	public function canCrop() {
		return false;
	}
}
