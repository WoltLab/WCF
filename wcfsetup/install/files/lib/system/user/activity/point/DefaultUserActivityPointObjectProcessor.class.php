<?php
namespace wcf\system\user\activity\point;

/**
 * Default implementation of a user activity point object processor.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	system.user.activity.point
 * @category	Community Framework
 */
class DefaultUserActivityPointObjectProcessor implements IUserActivityPointObjectProcessor {
	/**
	 * @see	wcf\system\user\activity\point\IUserActivityPointObject::countRequests();
	 */
	public function countRequests() {
		return 0;
	}
	
	/**
	 * @see	wcf\system\user\activity\point\IUserActivityPointObject::updateActivityPointEvents();
	 */
	public function updateActivityPointEvents($request) {
		return;
	}
}
