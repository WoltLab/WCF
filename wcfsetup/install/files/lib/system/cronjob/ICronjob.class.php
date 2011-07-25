<?php
namespace wcf\system\cronjob;

/**
 * Any Cronjob should implement this interface.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.cronjobs
 * @subpackage	system.cronjob
 * @category 	Community Framework
 */
interface ICronjob {
	/**
	 * To be called when executing the cronjob; the $data array e.g. might be used for passing
	 * meaningful values to the cronjob in order to reasonably avail multipleExecs.
	 * 
	 * @param	array		$data		This array should basically contain the dataset 
	 * 						associated to the executed cronjob, particularly 
	 * 						the date of the planned execution (the nextExec 
	 * 						field).
	 */
	public function execute(array $data);
}
