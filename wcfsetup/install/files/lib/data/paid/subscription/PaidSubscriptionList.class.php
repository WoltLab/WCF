<?php
namespace wcf\data\paid\subscription;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of paid subscriptions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Paid\Subscription
 *
 * @method	PaidSubscription	current()
 * @method	PaidSubscription[]	getObjects()
 * @method	PaidSubscription|null	search($objectID)
 * @property	PaidSubscription[]	$objects
 */
class PaidSubscriptionList extends DatabaseObjectList { }
