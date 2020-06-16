<?php
namespace wcf\system\upload;
use wcf\data\user\cover\photo\IUserCoverPhoto;
use wcf\data\user\cover\photo\UserCoverPhoto;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\ImageUtil;
use wcf\util\StringUtil;

/**
 * Save strategy for user cover photos.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Upload
 * @since	5.2
 */
class UserCoverPhotoUploadFileSaveStrategy implements IUploadFileSaveStrategy {
	/**
	 * @var integer
	 */
	protected $userID = 0;
	
	/**
	 * @var User
	 */
	protected $user;
	
	/**
	 * @var IUserCoverPhoto
	 */
	protected $coverPhoto;
	
	/**
	 * Creates a new instance of UserCoverPhotoUploadFileSaveStrategy.
	 *
	 * @param	integer		$userID
	 */
	public function __construct($userID = null) {
		$this->userID = ($userID ?: WCF::getUser()->userID);
		$this->user = ($this->userID != WCF::getUser()->userID ? new User($userID) : WCF::getUser());
	}
	
	/**
	 * @return IUserCoverPhoto
	 */
	public function getCoverPhoto() {
		return $this->coverPhoto;
	}
	
	/**
	 * @inheritDoc
	 */
	public function save(UploadFile $uploadFile) {
		if (!$uploadFile->getValidationErrorType()) {
			// rotate image if necessary
			/** @noinspection PhpUnusedLocalVariableInspection */
			$fileLocation = ImageUtil::fixOrientation($uploadFile->getLocation());
			
			// shrink cover photo if necessary
			try {
				$newFileLocation = ImageUtil::enforceDimensions($fileLocation, UserCoverPhoto::MAX_WIDTH, UserCoverPhoto::MAX_HEIGHT);
			}
			/** @noinspection PhpRedundantCatchClauseInspection */
			catch (SystemException $e) {
				$uploadFile->setValidationErrorType('maxSize');
				return;
			}
			
			if ($newFileLocation != $fileLocation) {
				// check dimensions (after shrink)
				$imageData = getimagesize($newFileLocation);
				if ($imageData[0] < UserCoverPhoto::MIN_WIDTH || $imageData[1] < UserCoverPhoto::MIN_HEIGHT) {
					$uploadFile->setValidationErrorType('tooLarge');
					return;
				}
				
				// check filesize (after shrink)
				if (@filesize($newFileLocation) > WCF::getSession()->getPermission('user.profile.coverPhoto.maxSize')) {
					$uploadFile->setValidationErrorType('tooLarge');
					return;
				}
			}
			$fileLocation = $newFileLocation;
			
			// delete old cover photo
			if ($this->user->coverPhotoHash) {
				UserProfileRuntimeCache::getInstance()->getObject($this->user->userID)->getCoverPhoto()->delete();
			}
			
			// update user
			(new UserEditor($this->user))->update([
				// always generate a new hash to invalidate the browser cache and to avoid filename guessing
				'coverPhotoHash' => StringUtil::getRandomID(),
				'coverPhotoExtension' => $uploadFile->getFileExtension()
			]);
			
			// force-reload the user profile to use a predictable code-path to fetch the cover photo
			UserProfileRuntimeCache::getInstance()->removeObject($this->user->userID);
			$userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->user->userID);
			$this->coverPhoto = $userProfile->getCoverPhoto();
			
			// check images directory and create subdirectory if necessary
			$dir = dirname($this->coverPhoto->getLocation());
			if (!@file_exists($dir)) {
				FileUtil::makePath($dir);
			}
			
			// move uploaded file
			if (!@copy($fileLocation, $this->coverPhoto->getLocation())) {
				// copy failed
				@unlink($fileLocation);
				(new UserEditor($this->user))->update([
					'coverPhotoHash' => '',
					'coverPhotoExtension' => ''
				]);
				$uploadFile->setValidationErrorType('uploadFailed');
			}
			
			@unlink($fileLocation);
		}
	}
}
