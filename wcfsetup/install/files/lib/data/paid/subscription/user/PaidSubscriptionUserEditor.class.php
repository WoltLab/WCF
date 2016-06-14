<?php
namespace wcf\data\paid\subscription\user;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit paid subscription users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Paid\Subscription\User
 * 
 * @method	PaidSubscriptionUser		getDecoratedObject()
 * @mixin	PaidSubscriptionUser
 */
class PaidSubscriptionUserEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PaidSubscriptionUser::class;
}
