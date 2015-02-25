<?php
namespace wcf\system\stat;

/**
 * Provides a general interface for statistic handler.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.stat
 * @category	Community Framework
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
