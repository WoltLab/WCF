<?php
namespace wcf\data\paid\subscription\user;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit paid subscription users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.paid.subscription.user
 * @category	Community Framework
 */
class PaidSubscriptionUserEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\paid\subscription\user\PaidSubscriptionUser';
}
