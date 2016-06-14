<?php
namespace wcf\system\stat;

/**
 * Provides a general interface for statistic handler.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Stat
 */
interface IStatDailyHandler {
	/**
	 * Returns the stats.
	 * 
	 * @param	integer		$date
	 * @return	array
	 */
	public function getData($date);
	
	/**
	 * Returns a formatted counter value.
	 * 
	 * @param	integer		$counter
	 * @return	mixed
	 */
	public function getFormattedCounter($counter);
}
