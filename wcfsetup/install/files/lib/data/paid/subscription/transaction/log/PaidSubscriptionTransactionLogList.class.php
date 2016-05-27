<?php
namespace wcf\data\paid\subscription\transaction\log;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of paid subscription transaction log entries.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.paid.subscription.transaction.log
 * @category	Community Framework
 *
 * @method	PaidSubscriptionTransactionLog		current()
 * @method	PaidSubscriptionTransactionLog[]	getObjects()
 * @method	PaidSubscriptionTransactionLog|null	search($objectID)
 * @property	PaidSubscriptionTransactionLog[]	$objects
 */
class PaidSubscriptionTransactionLogList extends DatabaseObjectList { }
