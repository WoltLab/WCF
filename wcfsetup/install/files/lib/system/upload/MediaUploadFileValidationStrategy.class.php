<?php
namespace wcf\system\upload;

/**
 * Upload file validation strategy implementation for media files.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.upload
 * @category	Community Framework
 * @since	2.2
 */
class MediaUploadFileValidationStrategy implements IUploadFileValidationStrategy {
	/**
	 * file type filters
	 * @var	array
	 */
	protected $fileTypeFilters = [];
	
	/**
	 * Creates a new instance of MediaUploadFileValidationStrategy.
	 * 
	 * @param	array	$fileTypeFilters
	 */
	public function __construct(array $fileTypeFilters) {
		$this->fileTypeFilters = $fileTypeFilters;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(UploadFile $uploadFile) {
		if ($uploadFile->getErrorCode()) {
			$uploadFile->setValidationErrorType('uploadFailed');
			return false;
		}
		
		if (!empty($this->fileTypeFilters['isImage']) && ($uploadFile->getImageData() === null || !preg_match('~^image/(gif|jpe?g|png)$~i', $uploadFile->getMimeType()))) {
			$uploadFile->setValidationErrorType('noImage');
			return false;
		}
		
		if (isset($this->fileTypeFilters['fileTypes'])) {
			foreach ($this->fileTypeFilters['fileTypes'] as $fileType) {
				if (substr($fileType, -1) == '*') {
					if (!preg_match('~^'.preg_quote(substr($fileType, 0, -1), '~').'~', $uploadFile->getMimeType())) {
						return false;
					}
				}
				else {
					if ($uploadFile->getMimeType() != $fileType) {
						$uploadFile->setValidationErrorType('noImage');
						return false;
					}
				}
			}
		}
		
		return true;
	}
}
