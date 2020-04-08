<?php
namespace wcf\data\user\avatar;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\upload\AvatarUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\HTTPRequest;
use wcf\util\ImageUtil;
use wcf\util\Url;

/**
 * Executes avatar-related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Avatar
 * 
 * @method	UserAvatar		create()
 * @method	UserAvatarEditor[]	getObjects()
 * @method	UserAvatarEditor	getSingleObject()
 */
class UserAvatarAction extends AbstractDatabaseObjectAction {
	/**
	 * currently edited avatar
	 * @var	UserAvatarEditor
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
		
		/** @noinspection PhpUndefinedMethodInspection */
		if (count($this->parameters['__files']->getFiles()) != 1) {
			throw new UserInputException('files');
		}
		
		// check max filesize, allowed file extensions etc.
		/** @noinspection PhpUndefinedMethodInspection */
		$this->parameters['__files']->validateFiles(new AvatarUploadFileValidationStrategy(PHP_INT_MAX, explode("\n", WCF::getSession()->getPermission('user.profile.avatar.allowedFileExtensions'))));
	}
	
	/**
	 * Handles uploaded attachments.
	 */
	public function upload() {
		/** @var UploadFile[] $files */
		/** @noinspection PhpUndefinedMethodInspection */
		$files = $this->parameters['__files']->getFiles();
		$userID = (!empty($this->parameters['userID']) ? intval($this->parameters['userID']) : WCF::getUser()->userID);
		$user = ($userID != WCF::getUser()->userID ? new User($userID) : WCF::getUser());
		$file = $files[0];
		
		try {
			if (!$file->getValidationErrorType()) {
				// shrink avatar if necessary
				$fileLocation = $this->enforceDimensions($file->getLocation());
				$imageData = getimagesize($fileLocation);
				
				$data = [
					'avatarName' => $file->getFilename(),
					'avatarExtension' => $file->getFileExtension(),
					'width' => $imageData[0],
					'height' => $imageData[1],
					'userID' => $userID,
					'fileHash' => sha1_file($fileLocation)
				];
				
				// create avatar
				$avatar = UserAvatarEditor::create($data);
				
				// check avatar directory
				// and create subdirectory if necessary
				$dir = dirname($avatar->getLocation());
				if (!@file_exists($dir)) {
					FileUtil::makePath($dir);
				}
				
				// move uploaded file
				if (@copy($fileLocation, $avatar->getLocation())) {
					@unlink($fileLocation);
					
					// delete old avatar
					if ($user->avatarID) {
						$action = new UserAvatarAction([$user->avatarID], 'delete');
						$action->executeAction();
					}
					
					// update user
					$userEditor = new UserEditor($user);
					$userEditor->update([
						'avatarID' => $avatar->avatarID,
						'enableGravatar' => 0
					]);
					
					// reset user storage
					UserStorageHandler::getInstance()->reset([$userID], 'avatar');
					
					// return result
					return [
						'avatarID' => $avatar->avatarID,
						'url' => $avatar->getURL(96)
					];
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
		
		return ['errorType' => $file->getValidationErrorType()];
	}
	
	/**
	 * Fetches an avatar from a remote server and sets it for given user.
	 */
	public function fetchRemoteAvatar() {
		$avatarID = 0;
		$filename = '';
		
		// fetch avatar from URL
		$imageData = null;
		try {
			$request = new HTTPRequest($this->parameters['url']);
			$request->execute();
			$reply = $request->getReply();
			$filename = FileUtil::getTemporaryFilename('avatar_');
			file_put_contents($filename, $reply['body']);
			
			$imageData = getimagesize($filename);
			if ($imageData === false) throw new SystemException('Downloaded file is not an image');
		}
		catch (\Exception $e) {
			// log exception unless this was caused by a non-image file being supplied
			if ($imageData !== false) {
				\wcf\functions\exception\logThrowable($e);
			}
			
			if (!empty($filename)) {
				@unlink($filename);
			}
			return;
		}
		
		// rescale avatar if required
		try {
			$newFilename = $this->enforceDimensions($filename);
			if ($newFilename !== $filename) @unlink($filename);
			$filename = $newFilename;
			
			$imageData = getimagesize($filename);
			if ($imageData === false) throw new SystemException('Rescaled file is not an image');
		}
		catch (\Exception $e) {
			@unlink($filename);
			return;
		}
		
		$tmp = Url::parse($this->parameters['url']);
		if (!isset($tmp['path'])) {
			@unlink($filename);
			return;
		}
		
		$tmp = pathinfo($tmp['path']);
		if (!isset($tmp['basename'])) {
			$tmp['basename'] = basename($filename);
		}
		
		$imageData = @getimagesize($filename);
		if ($imageData !== false) {
			$tmp['extension'] = ImageUtil::getExtensionByMimeType($imageData['mime']);
			
			if (!in_array($tmp['extension'], ['jpeg', 'jpg', 'png', 'gif'])) {
				@unlink($filename);
				return;
			}
		}
		else {
			@unlink($filename);
			return;
		}
		
		$data = [
			'avatarName' => $tmp['basename'],
			'avatarExtension' => $tmp['extension'],
			'width' => $imageData[0],
			'height' => $imageData[1],
			'userID' => $this->parameters['userEditor']->userID,
			'fileHash' => sha1_file($filename)
		];
		
		// create avatar
		$avatar = UserAvatarEditor::create($data);
		
		// check avatar directory
		// and create subdirectory if necessary
		$dir = dirname($avatar->getLocation());
		if (!@file_exists($dir)) {
			FileUtil::makePath($dir);
		}
		
		// move uploaded file
		if (@copy($filename, $avatar->getLocation())) {
			@unlink($filename);
			
			$avatarID = $avatar->avatarID;
		}
		else {
			@unlink($filename);
			
			// moving failed; delete avatar
			$editor = new UserAvatarEditor($avatar);
			$editor->delete();
		}
		
		// update user
		if ($avatarID) {
			/** @var UserEditor $userEditor */
			$userEditor = $this->parameters['userEditor'];
			
			$userEditor->update([
				'avatarID' => $avatarID,
				'enableGravatar' => 0
			]);
			
			// delete old avatar
			if ($userEditor->avatarID) {
				$action = new UserAvatarAction([$userEditor->avatarID], 'delete');
				$action->executeAction();
			}
		}
		
		// reset user storage
		UserStorageHandler::getInstance()->reset([$this->parameters['userEditor']->userID], 'avatar');
	}
	
	/**
	 * Enforces dimensions for given avatar.
	 * 
	 * @param	string		$filename
	 * @return	string
	 * @throws	UserInputException
	 */
	protected function enforceDimensions($filename) {
		$imageData = getimagesize($filename);
		if ($imageData[0] > UserAvatar::AVATAR_SIZE || $imageData[1] > UserAvatar::AVATAR_SIZE) {
			try {
				$adapter = ImageHandler::getInstance()->getAdapter();
				$adapter->loadFile($filename);
				$filename = FileUtil::getTemporaryFilename();
				$thumbnail = $adapter->createThumbnail(UserAvatar::AVATAR_SIZE, UserAvatar::AVATAR_SIZE, false);
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
}
