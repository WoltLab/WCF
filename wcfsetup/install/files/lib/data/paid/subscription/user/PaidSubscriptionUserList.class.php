<?php
namespace wcf\data\paid\subscription\user;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of paid subscription users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.paid.subscription.user
 * @category	Community Framework
 *
 * @method	PaidSubscriptionUser		current()
 * @method	PaidSubscriptionUser[]		getObjects()
 * @method	PaidSubscriptionUser|null	search($objectID)
 * @property	PaidSubscriptionUser[]		$objects
 */
class PaidSubscriptionUserList extends DatabaseObjectList { }
