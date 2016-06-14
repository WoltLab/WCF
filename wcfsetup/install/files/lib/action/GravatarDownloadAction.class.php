<?php
namespace wcf\action;
use wcf\data\user\avatar\Gravatar;
use wcf\data\user\avatar\UserAvatar;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;
use wcf\util\HTTPRequest;

/**
 * Downloads and caches gravatars.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 */
class GravatarDownloadAction extends AbstractAction {
	/**
	 * user id
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * user object
	 * @var	\wcf\data\user\User
	 */
	public $user = null;
	
	/**
	 * avatar size
	 * @var	integer
	 */
	public $size = 150;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		$this->user = new User($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
		
		if (!empty($_REQUEST['size'])) {
			$this->size = intval($_REQUEST['size']);
			if (!in_array($this->size, UserAvatar::$avatarThumbnailSizes)) {
				$this->size = 150;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		if ($this->user->enableGravatar) {
			$fileExtension = ($this->user->gravatarFileExtension ?: 'png');
			
			// try to use cached gravatar
			$cachedFilename = sprintf(Gravatar::GRAVATAR_CACHE_LOCATION, md5(mb_strtolower($this->user->email)), $this->size, $fileExtension);
			if (file_exists(WCF_DIR.$cachedFilename) && filemtime(WCF_DIR.$cachedFilename) > (TIME_NOW - (Gravatar::GRAVATAR_CACHE_EXPIRE * 86400))) {
				@header('Content-Type: image/png');
				@readfile(WCF_DIR.$cachedFilename);
				exit;
			}
			
			// try to download new version
			$gravatarURL = sprintf(Gravatar::GRAVATAR_BASE, md5(mb_strtolower($this->user->email)), $this->size, GRAVATAR_DEFAULT_TYPE);
			try {
				$request = new HTTPRequest($gravatarURL);
				$request->execute();
				$reply = $request->getReply();
				
				// get mime type and file extension
				$fileExtension = 'png';
				$mimeType = 'image/png';
				if (isset($reply['headers']['Content-Type'])) {
					switch ($reply['headers']['Content-Type']) {
						case 'image/jpeg':
							$mimeType = 'image/jpeg';
							$fileExtension = 'jpg';
						break;
						case 'image/gif':
							$mimeType = 'image/gif';
							$fileExtension = 'gif';
						break;
					}
				}
				
				// save file
				$cachedFilename = sprintf(Gravatar::GRAVATAR_CACHE_LOCATION, md5(mb_strtolower($this->user->email)), $this->size, $fileExtension);
				file_put_contents(WCF_DIR.$cachedFilename, $reply['body']);
				FileUtil::makeWritable(WCF_DIR.$cachedFilename);
				
				// update file extension
				if ($fileExtension != $this->user->gravatarFileExtension) {
					$editor = new UserEditor($this->user);
					$editor->update([
						'gravatarFileExtension' => $fileExtension
					]);
				}
				
				@header('Content-Type: '.$mimeType);
				@readfile(WCF_DIR.$cachedFilename);
				exit;
			}
			catch (SystemException $e) {
				// disable gravatar
				$editor = new UserEditor($this->user);
				$editor->update([
					'enableGravatar' => 0
				]);
			}
		}
		
		// fallback to default avatar
		@header('Content-Type: image/svg+xml');
		@readfile(WCF_DIR.'images/avatars/avatar-default.svg');
		exit;
	}
}
