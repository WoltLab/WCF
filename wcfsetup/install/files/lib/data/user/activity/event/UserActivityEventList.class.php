<?php
namespace wcf\data\user\activity\event;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of user activity events.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.activity.event
 * @category	Community Framework
 *
 * @method	UserActivityEvent		current()
 * @method	UserActivityEvent[]		getObjects()
 * @method	UserActivityEvent|null		search($objectID)
 * @property	UserActivityEvent[]		$objects
 */
class UserActivityEventList extends DatabaseObjectList { }
