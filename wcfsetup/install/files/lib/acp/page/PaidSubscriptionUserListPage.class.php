<?php
namespace wcf\acp\page;
use wcf\data\paid\subscription\user\PaidSubscriptionUserList;
use wcf\page\SortablePage;

/**
 * Shows the list of paid subscription users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	PaidSubscriptionUserList	$objectList
 */
class PaidSubscriptionUserListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription.user.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_PAID_SUBSCRIPTION'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.paidSubscription.canManageSubscription'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'userID';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['subscriptionUserID', 'userID', 'subscriptionID', 'startDate', 'endDate'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = PaidSubscriptionUserList::class;
	
	/**
	 * Initializes DatabaseObjectList instance.
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('isActive = ?', [1]);
		$this->objectList->sqlSelects = 'user_table.username, paid_subscription.title';
		$this->objectList->sqlJoins = "LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = paid_subscription_user.userID)";
		$this->objectList->sqlJoins .= " LEFT JOIN wcf".WCF_N."_paid_subscription paid_subscription ON (paid_subscription.subscriptionID = paid_subscription_user.subscriptionID)";
	}
}
