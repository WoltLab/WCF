<?php
namespace wcf\data\paid\subscription\user;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of paid subscription users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Paid\Subscription\User
 *
 * @method	PaidSubscriptionUser		current()
 * @method	PaidSubscriptionUser[]		getObjects()
 * @method	PaidSubscriptionUser|null	search($objectID)
 * @property	PaidSubscriptionUser[]		$objects
 */
class PaidSubscriptionUserList extends DatabaseObjectList { }
