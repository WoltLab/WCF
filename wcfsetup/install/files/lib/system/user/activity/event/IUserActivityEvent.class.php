<?php
namespace wcf\system\user\activity\event;
use wcf\data\user\activity\event\ViewableUserActivityEvent;

/**
 * Default interface for user activity events.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Activity\Event
 */
interface IUserActivityEvent {
	/**
	 * Prepares a list of events for output.
	 * 
	 * @param	ViewableUserActivityEvent[]	$events
	 */
	public function prepare(array $events);
}
