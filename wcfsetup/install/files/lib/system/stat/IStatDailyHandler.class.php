<?php
namespace wcf\system\stat;

/**
 * Provides a general interface for statistic handler.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
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
}
