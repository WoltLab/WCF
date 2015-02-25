<?php
namespace wcf\data\paid\subscription\transaction\log;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit paid subscription transaction log entries.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.paid.subscription.transaction.log
 * @category	Community Framework
 */
class PaidSubscriptionTransactionLogEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLog';
}
