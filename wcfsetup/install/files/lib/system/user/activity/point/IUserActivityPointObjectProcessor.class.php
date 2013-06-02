<?php
namespace wcf\system\user\activity\point;

/**
 * Every UserActivityPointObjectProcessor has to implement this interface.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.user.activity.point
 * @category	Community Framework
 */
interface IUserActivityPointObjectProcessor {
	/**
	 * This method has to return the amount of requests needed to completely
	 * process this UserActivityPointObject.
	 * 
	 * @return	integer
	 */
	public function countRequests();
	
	/**
	 * This method updates the activityPointEvents. $request will be an integer
	 * between 0 (first request) and the number returned by countRequests() minus 1.
	 * 
	 * @param	integer		$request
	 */
	public function updateActivityPointEvents($request);
}
