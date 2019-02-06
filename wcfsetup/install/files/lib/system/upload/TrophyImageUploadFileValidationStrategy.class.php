<?php
namespace wcf\system\upload;
use wcf\util\ImageUtil;

/**
 * Upload file validation strategy implementation for trophy images.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Upload
 * @since	3.1
 */
class TrophyImageUploadFileValidationStrategy implements IUploadFileValidationStrategy {
	/**
	 * minimum trophy image width and height
	 * @var integer
	 */
	const MIN_TROPHY_IMAGE_SIZE = 64;
	
	/**
	 * @inheritDoc
	 */
	public function validate(UploadFile $uploadFile) {
		if ($uploadFile->getErrorCode()) {
			$uploadFile->setValidationErrorType('uploadFailed');
			return false;
		}
		
		if ($uploadFile->getImageData() === null) {
			$uploadFile->setValidationErrorType('noImage');
			return false;
		}
		
		if ($uploadFile->getImageData()['width'] != $uploadFile->getImageData()['height']) {
			$uploadFile->setValidationErrorType('notSquared');
			return false;
		}
		
		if ($uploadFile->getImageData()['width'] < self::MIN_TROPHY_IMAGE_SIZE) {
			$uploadFile->setValidationErrorType('tooSmall');
			return false; 
		}
		
		if (!ImageUtil::checkImageContent($uploadFile->getLocation())) {
			$uploadFile->setValidationErrorType('noImage');
			return false; 
		}
		
		if (!ImageUtil::isImage($uploadFile->getLocation(), $uploadFile->getFilename())) {
			$uploadFile->setValidationErrorType('noImage');
			return false; 
		}
		
		return true;
	}
}
