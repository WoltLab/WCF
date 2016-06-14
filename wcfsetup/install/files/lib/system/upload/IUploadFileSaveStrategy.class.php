<?php
namespace wcf\system\upload;

/**
 * Interface for file upload save strategies.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Upload
 */
interface IUploadFileSaveStrategy {
	/**
	 * Saves the given file.
	 * 
	 * @param	\wcf\system\upload\UploadFile		$uploadFile
	 */
	public function save(UploadFile $uploadFile);
}
