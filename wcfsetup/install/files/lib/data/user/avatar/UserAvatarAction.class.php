<?php
namespace wcf\data\user\avatar;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfile;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\upload\AvatarUploadFileValidationStrategy;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\HTTPRequest;

/**
 * Executes avatar-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.avatar
 * @category	Community Framework
 */
class UserAvatarAction extends AbstractDatabaseObjectAction {
	/**
	 * currently edited avatar
	 * @var	\wcf\data\user\avatar\UserAvatarEditor
	 */
	public $avatar = null;
	
	/**
	 * Validates the upload action.
	 */
	public function validateUpload() {
		$this->readInteger('userID', true);
		
		if ($this->parameters['userID']) {
			if (!WCF::getSession()->getPermission('admin.user.canEditUser')) {
				throw new PermissionDeniedException();
			}
			
			$user = new User($this->parameters['userID']);
			if (!$user->userID) {
				throw new IllegalLinkException();
			}
		}
		
		// check upload permissions
		if (!WCF::getSession()->getPermission('user.profile.avatar.canUploadAvatar') || WCF::getUser()->disableAvatar) {
			throw new PermissionDeniedException();
		}
		
		if (count($this->parameters['__files']->getFiles()) != 1) {
			throw new UserInputException('files');
		}
		
		// check max filesize, allowed file extensions etc.
		$this->parameters['__files']->validateFiles(new AvatarUploadFileValidationStrategy(PHP_INT_MAX, explode("\n", WCF::getSession()->getPermission('user.profile.avatar.allowedFileExtensions'))));
	}
	
	/**
	 * Handles uploaded attachments.
	 */
	public function upload() {
		// save files
		$files = $this->parameters['__files']->getFiles();
		$userID = (!empty($this->parameters['userID']) ? intval($this->parameters['userID']) : WCF::getUser()->userID);
		$user = ($userID != WCF::getUser()->userID ? new User($userID) : WCF::getUser());
		$file = $files[0];
		
		try {
			if (!$file->getValidationErrorType()) {
				// shrink avatar if necessary
				$fileLocation = $this->enforceDimensions($file->getLocation());
				$imageData = getimagesize($fileLocation);
				
				$data = array(
					'avatarName' => $file->getFilename(),
					'avatarExtension' => $file->getFileExtension(),
					'width' => $imageData[0],
					'height' => $imageData[1],
					'userID' => $userID,
					'fileHash' => sha1_file($fileLocation)
				);
				
				// create avatar
				$avatar = UserAvatarEditor::create($data);
				
				// check avatar directory
				// and create subdirectory if necessary
				$dir = dirname($avatar->getLocation());
				if (!@file_exists($dir)) {
					FileUtil::makePath($dir, 0777);
				}
				
				// move uploaded file
				if (@copy($fileLocation, $avatar->getLocation())) {
					@unlink($fileLocation);
					
					// create thumbnails
					$action = new UserAvatarAction(array($avatar), 'generateThumbnails');
					$action->executeAction();
					
					// delete old avatar
					if ($user->avatarID) {
						$action = new UserAvatarAction(array($user->avatarID), 'delete');
						$action->executeAction();
					}
					
					// update user
					$userEditor = new UserEditor($user);
					$userEditor->update(array(
						'avatarID' => $avatar->avatarID,
						'enableGravatar' => 0
					));
					
					// reset user storage
					UserStorageHandler::getInstance()->reset(array($userID), 'avatar');
					
					// return result
					return array(
						'avatarID' => $avatar->avatarID,
						'canCrop' => $avatar->canCrop(),
						'url' => $avatar->getURL(96)
					);
				}
				else {
					// moving failed; delete avatar
					$editor = new UserAvatarEditor($avatar);
					$editor->delete();
					throw new UserInputException('avatar', 'uploadFailed');
				}
			}
		}
		catch (UserInputException $e) {
			$file->setValidationErrorType($e->getType());
		}
		
		return array('errorType' => $file->getValidationErrorType());
	}
	
	/**
	 * Generates the thumbnails of the avatars in all needed sizes.
	 */
	public function generateThumbnails() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->objects as $avatar) {
			$adapter = ImageHandler::getInstance()->getAdapter();
			$adapter->loadFile($avatar->getLocation());
			
			foreach (UserAvatar::$avatarThumbnailSizes as $size) {
				if ($avatar->width <= $size && $avatar->height <= $size) break 2;
				
				$thumbnail = $adapter->createThumbnail($size, $size, false);
				$adapter->writeImage($thumbnail, $avatar->getLocation($size));
			}
		}
	}
	
	/**
	 * Fetches an avatar from a remote server and sets it for given user.
	 */
	public function fetchRemoteAvatar() {
		$avatarID = 0;
		$filename = '';
		
		// fetch avatar from URL
		try {
			$request = new HTTPRequest($this->parameters['url']);
			$request->execute();
			$reply = $request->getReply();
			$filename = FileUtil::getTemporaryFilename('avatar_');
			file_put_contents($filename, $reply['body']);
		}
		catch (\Exception $e) {
			if (!empty($filename)) {
				@unlink($filename);
			}
		}
		
		// rescale avatar if required
		try {
			$filename = $this->enforceDimensions($filename);
		}
		catch (\Exception $e) { /* ignore errors */ }
		
		$imageData = getimagesize($filename);
		$tmp = parse_url($this->parameters['url']);
		$tmp = pathinfo($tmp['path']);
		
		$data = array(
			'avatarName' => $tmp['basename'],
			'avatarExtension' => $tmp['extension'],
			'width' => $imageData[0],
			'height' => $imageData[1],
			'userID' => $this->parameters['userEditor']->userID,
			'fileHash' => sha1_file($filename)
		);
		
		// create avatar
		$avatar = UserAvatarEditor::create($data);
		
		// check avatar directory
		// and create subdirectory if necessary
		$dir = dirname($avatar->getLocation());
		if (!@file_exists($dir)) {
			FileUtil::makePath($dir, 0777);
		}
		
		// move uploaded file
		if (@copy($filename, $avatar->getLocation())) {
			@unlink($filename);
			
			// create thumbnails
			$action = new UserAvatarAction(array($avatar), 'generateThumbnails');
			$action->executeAction();
			
			$avatarID = $avatar->avatarID;
		}
		else {
			// moving failed; delete avatar
			$editor = new UserAvatarEditor($avatar);
			$editor->delete();
		}
		
		// update user
		if ($avatarID) {
			$this->parameters['userEditor']->update(array(
				'avatarID' => $avatarID,
				'enableGravatar' => 0
			));
			
			// delete old avatar
			if ($this->parameters['userEditor']->avatarID) {
				$action = new UserAvatarAction(array($this->parameters['userEditor']->avatarID), 'delete');
				$action->executeAction();
			}
		}
		
		// reset user storage
		UserStorageHandler::getInstance()->reset(array($this->parameters['userEditor']->userID), 'avatar');
	}
	
	/**
	 * Enforces dimensions for given avatar.
	 * 
	 * @param	string		$filename
	 * @return	string
	 */
	protected function enforceDimensions($filename) {
		$imageData = getimagesize($filename);
		if ($imageData[0] > MAX_AVATAR_WIDTH || $imageData[1] > MAX_AVATAR_HEIGHT) {
			try {
				$obtainDimensions = true;
				if (MAX_AVATAR_WIDTH / $imageData[0] < MAX_AVATAR_HEIGHT / $imageData[1]) {
					if (round($imageData[1] * (MAX_AVATAR_WIDTH / $imageData[0])) < 48) $obtainDimensions = false;
				}
				else {
					if (round($imageData[0] * (MAX_AVATAR_HEIGHT / $imageData[1])) < 48) $obtainDimensions = false;
				}
				
				$adapter = ImageHandler::getInstance()->getAdapter();
				$adapter->loadFile($filename);
				$filename = FileUtil::getTemporaryFilename();
				$thumbnail = $adapter->createThumbnail(MAX_AVATAR_WIDTH, MAX_AVATAR_HEIGHT, $obtainDimensions);
				$adapter->writeImage($thumbnail, $filename);
			}
			catch (SystemException $e) {
				throw new UserInputException('avatar', 'tooLarge');
			}
			
			// check filesize (after shrink)
			if (@filesize($filename) > WCF::getSession()->getPermission('user.profile.avatar.maxSize')) {
				throw new UserInputException('avatar', 'tooLarge');
			}
		}
		
		return $filename;
	}
	
	/**
	 * Validates the 'getCropDialog' action.
	 */
	public function validateGetCropDialog() {
		$this->avatar = $this->getSingleObject();
	}
	
	/**
	 * Returns the data for the dialog to crop an avatar.
	 * 
	 * @return	array
	 */
	public function getCropDialog() {
		return array(
			'cropX' => $this->avatar->cropX,
			'cropY' => $this->avatar->cropY,
			'template' => WCF::getTPL()->fetch('avatarCropDialog', 'wcf', array(
				'avatar' => $this->avatar
			))
		);
	}
	
	/**
	 * Validates the 'cropAvatar' action.
	 */
	public function validateCropAvatar() {
		$this->avatar = $this->getSingleObject();
		
		// check if user can edit the given avatar
		if ($this->avatar->userID != WCF::getUser()->userID && !WCF::getSession()->getPermission('admin.user.canEditUser')) {
			throw new PermissionDeniedException();
		}
		
		if (!WCF::getSession()->getPermission('user.profile.avatar.canUploadAvatar') || UserProfile::getUserProfile($this->avatar->userID)->disableAvatar) {
			throw new PermissionDeniedException();
		}
		
		// check parameters
		$this->readInteger('cropX', true);
		$this->readInteger('cropY', true);
		
		if ($this->parameters['cropX'] < 0 || $this->parameters['cropX'] > $this->avatar->width - UserAvatar::$maxThumbnailSize) {
			throw new UserInputException('cropX');
		}
		if ($this->parameters['cropY'] < 0 || $this->parameters['cropY'] > $this->avatar->height - UserAvatar::$maxThumbnailSize) {
			throw new UserInputException('cropY');
		}
	}
	
	/**
	 * Craps an avatar.
	 */
	public function cropAvatar() {
		// created clipped avatar as base for new thumbnails
		$adapter = ImageHandler::getInstance()->getAdapter();
		$adapter->loadFile($this->avatar->getLocation());
		$adapter->clip($this->parameters['cropX'], $this->parameters['cropY'], UserAvatar::$maxThumbnailSize, UserAvatar::$maxThumbnailSize);
		
		// update thumbnails
		foreach (UserAvatar::$avatarThumbnailSizes as $size) {
			$thumbnail = $adapter->createThumbnail($size, $size);
			$adapter->writeImage($thumbnail, $this->avatar->getLocation($size));
		}
		
		// update database entry
		$this->avatar->update(array(
			'cropX' => $this->parameters['cropX'],
			'cropY' => $this->parameters['cropY']
		));
		
		return array(
			'url' => $this->avatar->getURL(96)
		);
	}
}
