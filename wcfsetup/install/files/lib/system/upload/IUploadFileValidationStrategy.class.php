<?php
namespace wcf\system\upload;

/**
 * Interface for file upload validation strategies.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Upload
 */
interface IUploadFileValidationStrategy {
	/**
	 * Validates the given file and returns true on success, otherwise false.
	 * 
	 * @param	\wcf\system\upload\UploadFile		$uploadFile
	 * @return	boolean
	 */
	public function validate(UploadFile $uploadFile);
}
