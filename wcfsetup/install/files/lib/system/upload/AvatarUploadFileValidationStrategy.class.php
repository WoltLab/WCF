<?php
namespace wcf\system\upload;
use wcf\data\user\avatar\UserAvatar;
use wcf\system\exception\SystemException;

/**
 * Validation strategy for avatar uploads.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.upload
 * @category	Community Framework
 */
class AvatarUploadFileValidationStrategy extends DefaultUploadFileValidationStrategy {
	/**
	 * @see	\wcf\system\upload\IUploadFileValidationStrategy::validate()
	 */
	public function validate(UploadFile $uploadFile) {
		if (!parent::validate($uploadFile)) return false;
		
		// check image size
		try {
			$imageData = getimagesize($uploadFile->getLocation());
			if ($imageData[0] < UserAvatar::MIN_AVATAR_SIZE || $imageData[1] < UserAvatar::MIN_AVATAR_SIZE) {
				$uploadFile->setValidationErrorType('tooSmall');
				return false;
			}
		}
		catch (SystemException $e) {
			$uploadFile->setValidationErrorType('badImage');
			return false;
		}
		
		return true;
	}
}
