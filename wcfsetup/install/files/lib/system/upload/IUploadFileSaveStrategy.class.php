<?php
namespace wcf\system\upload;

/**
 * Interface for file upload save strategies.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.upload
 * @category	Community Framework
 */
interface IUploadFileSaveStrategy {
	/**
	 * Saves the given file.
	 * 
	 * @param	\wcf\system\upload\UploadFile		$uploadFile
	 */
	public function save(UploadFile $uploadFile);
}
