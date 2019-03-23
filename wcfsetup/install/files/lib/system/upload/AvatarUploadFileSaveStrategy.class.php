<?php
namespace wcf\system\upload;
use wcf\data\user\avatar\UserAvatar;
use wcf\data\user\avatar\UserAvatarAction;
use wcf\data\user\avatar\UserAvatarEditor;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\exception\SystemException;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\ImageUtil;

/**
 * Save strategy for avatar uploads. 
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Upload
 * @since	5.2
 */
class AvatarUploadFileSaveStrategy implements IUploadFileSaveStrategy {
	/**
	 * @var integer
	 */
	protected $userID = 0;
	
	/**
	 * @var User
	 */
	protected $user;
	
	/**
	 * @var UserAvatar
	 */
	protected $avatar;
	
	/**
	 * Creates a new instance of AvatarUploadFileSaveStrategy.
	 *
	 * @param	integer		$userID
	 */
	public function __construct($userID = null) {
		$this->userID = ($userID ?: WCF::getUser()->userID);
		$this->user = ($this->userID != WCF::getUser()->userID ? new User($userID) : WCF::getUser());
	}
	
	/**
	 * @return UserAvatar
	 */
	public function getAvatar() {
		return $this->avatar;
	}
	
	/**
	 * @inheritDoc
	 */
	public function save(UploadFile $uploadFile) {
		if (!$uploadFile->getValidationErrorType()) {
			// rotate avatar if necessary
			/** @noinspection PhpUnusedLocalVariableInspection */
			$fileLocation = ImageUtil::fixOrientation($uploadFile->getLocation());
			
			// shrink avatar if necessary
			try {
				$fileLocation = ImageUtil::enforceDimensions($fileLocation, UserAvatar::AVATAR_SIZE, UserAvatar::AVATAR_SIZE, false);
			}
			/** @noinspection PhpRedundantCatchClauseInspection */
			catch (SystemException $e) {
				$uploadFile->setValidationErrorType('tooLarge');
				return;
			}
			
			// check filesize (after shrink)
			if (@filesize($fileLocation) > WCF::getSession()->getPermission('user.profile.avatar.maxSize')) {
				$uploadFile->setValidationErrorType('tooLarge');
				return;
			}
			
			$imageData = getimagesize($fileLocation);
			$data = [
				'avatarName' => $uploadFile->getFilename(),
				'avatarExtension' => $uploadFile->getFileExtension(),
				'width' => $imageData[0],
				'height' => $imageData[1],
				'userID' => $this->userID,
				'fileHash' => sha1_file($fileLocation)
			];
			
			// create avatar
			$this->avatar = UserAvatarEditor::create($data);
			
			// check avatar directory
			// and create subdirectory if necessary
			$dir = dirname($this->avatar->getLocation());
			if (!@file_exists($dir)) {
				FileUtil::makePath($dir);
			}
			
			// move uploaded file
			if (@copy($fileLocation, $this->avatar->getLocation())) {
				@unlink($fileLocation);
				
				// delete old avatar
				if ($this->user->avatarID) {
					$action = new UserAvatarAction([$this->user->avatarID], 'delete');
					$action->executeAction();
				}
				
				// update user
				$userEditor = new UserEditor($this->user);
				$userEditor->update([
					'avatarID' => $this->avatar->avatarID,
					'enableGravatar' => 0
				]);
				
				// reset user storage
				UserStorageHandler::getInstance()->reset([$this->userID], 'avatar');
			}
			else {
				// moving failed; delete avatar
				$editor = new UserAvatarEditor($this->avatar);
				$editor->delete();
				
				$uploadFile->setValidationErrorType('uploadFailed');
			}
		}
	}
}
