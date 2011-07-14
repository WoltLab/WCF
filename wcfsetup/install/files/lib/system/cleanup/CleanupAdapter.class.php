<?php
namespace wcf\system\cleanup;

/**
 * Default interface for cleanup adapters.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cleanup
 * @category 	Community Framework
 */
interface CleanupAdapter {
	/**
	 * Executes this adapter.
	 * 
	 * @param	array		$objectIDs
	 */
	public function execute(array $objectIDs);
}
?>