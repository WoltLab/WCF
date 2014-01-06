<?php
namespace wcf\action;
use wcf\data\user\avatar\Gravatar;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;

/**
 * Downloads and caches gravatars.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
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
	 * @see	\wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		$this->user = new User($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
		
		if (!empty($_REQUEST['size'])) $this->size = intval($_REQUEST['size']); 
	}
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		if ($this->user->enableGravatar) {
			// try to use cached gravatar
			$cachedFilename = sprintf(Gravatar::GRAVATAR_CACHE_LOCATION, md5(mb_strtolower($this->user->email)), $this->size);
			if (file_exists(WCF_DIR.$cachedFilename) && filemtime(WCF_DIR.$cachedFilename) > (TIME_NOW - (Gravatar::GRAVATAR_CACHE_EXPIRE * 86400))) {
				@header('Content-Type: image/png');
				@readfile(WCF_DIR.$cachedFilename);
				exit;
			}
			
			// try to download new version
			$gravatarURL = sprintf(Gravatar::GRAVATAR_BASE, md5(mb_strtolower($this->user->email)), $this->size, '404');
			try {
				$tmpFile = FileUtil::downloadFileFromHttp($gravatarURL, 'gravatar');
				copy($tmpFile, WCF_DIR.$cachedFilename);
				@unlink($tmpFile);
				FileUtil::makeWritable(WCF_DIR.$cachedFilename);
				
				@header('Content-Type: image/png');
				@readfile(WCF_DIR.$cachedFilename);
				exit;
			}
			catch (SystemException $e) {
				// disable gravatar
				$editor = new UserEditor($this->user);
				$editor->update(array(
					'enableGravatar' => 0
				));
			}
		}
		
		// fallback to default avatar
		@header('Content-Type: image/svg+xml');
		@readfile(WCF_DIR.'images/avatars/avatar-default.svg');
		exit;
	}
}
