<?php
namespace wcf\system\upload;

/**
 * Provides a default implementation for validation strategies.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.upload
 * @category	Community Framework
 */
class DefaultUploadFileValidationStrategy implements IUploadFileValidationStrategy {
	/**
	 * allowed max size
	 * @var	integer
	 */
	protected $maxFilesize = 0;
	
	/**
	 * allowed file extensions
	 * @var	array<string>
	 */
	protected $fileExtensions = array();
	
	/**
	 * Creates a new DefaultUploadFileValidationStrategy object.
	 * 
	 * @param	integer		$maxFilesize
	 * @param	array<string>	$fileExtensions
	 */
	public function __construct($maxFilesize, array $fileExtensions) {
		$this->maxFilesize = $maxFilesize;
		$this->fileExtensions = $fileExtensions;
	}
	
	/**
	 * @see	wcf\system\upload\IUploadFileValidationStrategy::validate()
	 */
	public function validate(UploadFile $uploadFile) {
		if ($uploadFile->getErrorCode() != 0) {
			$uploadFile->setValidationErrorType('uploadFailed');
			return false;
		}
		
		if ($uploadFile->getFilesize() > $this->maxFilesize) {
			$uploadFile->setValidationErrorType('tooLarge');
			return false;
		}
		
		if (!in_array($uploadFile->getFileExtension(), $this->fileExtensions)) {
			$uploadFile->setValidationErrorType('invalidExtension');
			return false;
		}
		
		return true;
	}
}
