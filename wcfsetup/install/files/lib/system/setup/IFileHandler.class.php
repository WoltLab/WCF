<?php
namespace wcf\system\setup;

/**
 * Logs files and checks their overwriting rights.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.setup
 * @category	Community Framework
 */
interface IFileHandler {
	/**
	 * Checks the overwriting rights of the given files.
	 * 
	 * @param	array		$files
	 */
	public function checkFiles(array $files);
	
	/**
	 * Logs the given list of files.
	 * 
	 * @param	array		$files
	 */
	public function logFiles(array $files);
}
