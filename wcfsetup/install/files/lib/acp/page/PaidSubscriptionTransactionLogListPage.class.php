<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the list of paid subscription transactions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PaidSubscriptionTransactionLogListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription.transactionLog.list';
	
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
	public $defaultSortField = 'logTime';
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortOrder
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('logID', 'subscriptionUserID', 'userID', 'subscriptionID', 'paymentMethodObjectTypeID', 'logTime', 'transactionID', 'logMessage');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLogList';
	
	/**
	 * transaction id
	 * @var	string
	 */
	public $transactionID = '';
	
	/**
	 * username
	 * @var	string
	 */
	public $username = '';
	
	/**
	 * subscription id
	 * @var	integer
	 */
	public $subscriptionID = 0;
	
	/**
	 * @see	\wcf\page\AbstractPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['transactionID'])) $this->transactionID = StringUtil::trim($_REQUEST['transactionID']);
		if (isset($_REQUEST['username'])) $this->username = StringUtil::trim($_REQUEST['username']);
		if (isset($_REQUEST['subscriptionID'])) $this->subscriptionID = intval($_REQUEST['subscriptionID']);
	}
	
	/**
	 * Initializes DatabaseObjectList instance.
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		if ($this->transactionID) {
			$this->objectList->getConditionBuilder()->add('paid_subscription_transaction_log.transactionID LIKE ?', array('%' . $this->transactionID . '%'));
		}
		if ($this->username) {
			$this->objectList->getConditionBuilder()->add('paid_subscription_transaction_log.userID IN (SELECT userID FROM wcf'.WCF_N.'_user WHERE username LIKE ?)', array('%' . $this->username . '%'));
		}
		if ($this->subscriptionID) {
			$this->objectList->getConditionBuilder()->add('paid_subscription_transaction_log.subscriptionID = ?', array($this->subscriptionID));
		}
		
		$this->objectList->sqlSelects = 'user_table.username, paid_subscription.title';
		$this->objectList->sqlJoins = "LEFT JOIN wcf".WCF_N."_user user_table ON (user_table.userID = paid_subscription_transaction_log.userID)";
		$this->objectList->sqlJoins .= " LEFT JOIN wcf".WCF_N."_paid_subscription paid_subscription ON (paid_subscription.subscriptionID = paid_subscription_transaction_log.subscriptionID)";
	}
	
	/**
	 * @see	\wcf\page\AbstractPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'transactionID' => $this->transactionID,
			'username' => $this->username,
			'subscriptionID' => $this->subscriptionID,
			'availableSubscriptions' => PaidSubscriptionCacheBuilder::getInstance()->getData()
		));
	}
}
