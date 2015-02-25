<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;

/**
 * Shows the list of paid subscription users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PaidSubscriptionUserListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription.user.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_PAID_SUBSCRIPTION');
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.paidSubscription.canManageSubscription');
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'userID';
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('subscriptionUserID', 'userID', 'subscriptionID', 'startDate', 'endDate');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\paid\subscription\user\PaidSubscriptionUserList';
	
	/**
	 * Initializes DatabaseObjectList instance.
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add('isActive = ?', array(1));
		$this->objectList->sqlSelects = 'user_table.username, paid_subscription.title';
		$this->objectList->sqlJoins = "LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = paid_subscription_user.userID)";
		$this->objectList->sqlJoins .= " LEFT JOIN wcf".WCF_N."_paid_subscription paid_subscription ON (paid_subscription.subscriptionID = paid_subscription_user.subscriptionID)";
	}
}
