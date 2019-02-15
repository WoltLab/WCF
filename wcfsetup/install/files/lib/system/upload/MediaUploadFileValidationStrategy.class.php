<?php
namespace wcf\system\upload;

/**
 * Upload file validation strategy implementation for media files.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Upload
 * @since	3.0
 */
class MediaUploadFileValidationStrategy implements IUploadFileValidationStrategy {
	/**
	 * if `true`, only images are valid
	 * @var	boolean
	 */
	protected $imagesOnly = false;
	
	/**
	 * Creates a new instance of MediaUploadFileValidationStrategy.
	 * 
	 * @param	boolean		$imagesOnly
	 */
	public function __construct($imagesOnly) {
		$this->imagesOnly = $imagesOnly;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(UploadFile $uploadFile) {
		if ($uploadFile->getErrorCode()) {
			$uploadFile->setValidationErrorType('uploadFailed');
			return false;
		}
		
		if ($this->imagesOnly && $uploadFile->getImageData() === null) {
			$uploadFile->setValidationErrorType('noImage');
			return false;
		}
		
		return true;
	}
}
