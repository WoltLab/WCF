<?php
namespace wcf\system\upload;
use wcf\system\exception\SystemException;

/**
 * Validation strategy for avatar uploads.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
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
		
		// get image size
		try {
			$imageData = getimagesize($uploadFile->getLocation());
			if ($imageData[0] < 48 || $imageData[1] < 48) {
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
