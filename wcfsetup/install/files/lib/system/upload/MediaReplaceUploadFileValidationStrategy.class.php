<?php
namespace wcf\system\upload;
use wcf\data\media\Media;

/**
 * Upload file validation strategy implementation for media file replacements.
 * 
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Upload
 * @since       5.3
 */
class MediaReplaceUploadFileValidationStrategy extends MediaUploadFileValidationStrategy {
	/**
	 * media whose file will be replaced
	 * @var Media
	 */
	protected $media;
	
	/**
	 * Creates a new instance of MediaReplaceUploadFileValidationStrategy.
	 * 
	 * @param       Media           $media
	 */
	public function __construct(Media $media) {
		$this->media = $media;
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(UploadFile $uploadFile) {
		if (!parent::validate($uploadFile)) {
			return false;
		}
		
		if ($this->media->fileType !== $uploadFile->getMimeType()) {
			$uploadFile->setValidationErrorType('differentFileType');
			return false;
		}
		
		if (strtolower(pathinfo($this->media->filename, PATHINFO_EXTENSION)) !== strtolower($uploadFile->getFileExtension())) {
			$uploadFile->setValidationErrorType('differentFileExtension');
			return false;
		}
		
		return true;
	}
}
