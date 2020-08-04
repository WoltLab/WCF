<?php
namespace wcf\system\upload;
use wcf\data\user\cover\photo\UserCoverPhoto;
use wcf\system\image\ImageHandler;
use wcf\system\WCF;
use wcf\util\ExifUtil;

/**
 * Upload file validation strategy implementation for user cover photos.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Upload
 * @since	3.1
 */
class UserCoverPhotoUploadFileValidationStrategy implements IUploadFileValidationStrategy {
	/**
	 * list of allowed file extensions
	 * @var string[]
	 */
	public static $allowedExtensions = ['gif', 'jpg', 'jpeg', 'png'];
	
	/**
	 * @inheritDoc
	 */
	public function validate(UploadFile $uploadFile) {
		if ($uploadFile->getErrorCode() != 0) {
			$uploadFile->setValidationErrorType('uploadFailed');
			
			return false;
		}
		
		// validate file extension
		if (!in_array($uploadFile->getFileExtension(), self::$allowedExtensions)) {
			$uploadFile->setValidationErrorType('fileExtension');
			
			return false;
		}
		
		// check image data
		$imageData = $uploadFile->getImageData();
		if ($imageData === null) {
			$uploadFile->setValidationErrorType('uploadFailed');
			
			return false;
		}
		
		$height = $imageData['height'];
		$width = $imageData['width'];
		$orientation = ExifUtil::getOrientation(ExifUtil::getExifData($uploadFile->getLocation()));
		
		// flip height and width if image is rotated 90 or 270 degrees
		if ($orientation == ExifUtil::ORIENTATION_90_ROTATE || $orientation == ExifUtil::ORIENTATION_270_ROTATE) {
			$height = $imageData['width'];
			$width = $imageData['height'];
		}
		
		// estimate if there is enough memory for a resize, if there is,
		// we do not need to mark an image which is too high or too wide
		// as invalid
		$sufficientMemory = ImageHandler::getInstance()->getAdapter()->checkMemoryLimit($width, $height, $imageData['mimeType']);
		
		// check width
		if ($width < UserCoverPhoto::MIN_WIDTH) {
			$uploadFile->setValidationErrorType('minWidth');
			
			return false;
		}
		else if (!$sufficientMemory && $width > UserCoverPhoto::MAX_WIDTH) {
			$uploadFile->setValidationErrorType('maxWidth');
			
			return false;
		}
		
		// check height
		if ($height < UserCoverPhoto::MIN_HEIGHT) {
			$uploadFile->setValidationErrorType('minHeight');
			
			return false;
		}
		else if (!$sufficientMemory && $height > UserCoverPhoto::MAX_HEIGHT) {
			$uploadFile->setValidationErrorType('maxHeight');
			
			return false;
		}
		
		// check file size if image will not be resized automatically
		// the file size of resized images is checked in ImageAction::processImages() 
		$filesize = $uploadFile->getFilesize();
		if ($width <= UserCoverPhoto::MAX_WIDTH && $height <= UserCoverPhoto::MAX_HEIGHT) {
			if ($filesize > WCF::getSession()->getPermission('user.profile.coverPhoto.maxSize')) {
				$uploadFile->setValidationErrorType('maxSize');
				
				return false;
			}
		}
		
		return true;
	}
}
