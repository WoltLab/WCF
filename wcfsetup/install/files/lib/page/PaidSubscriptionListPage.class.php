<?php
namespace wcf\page;
use wcf\data\paid\subscription\user\PaidSubscriptionUserList;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\menu\user\UserMenu;
use wcf\system\WCF;

/**
 * Shows a list of the available paid subscriptions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class PaidSubscriptionListPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededModules
	 */
	public $neededModules = array('MODULE_PAID_SUBSCRIPTION');
	
	/**
	 * list of available paid subscriptions
	 * @var	array
	 */
	public $subscriptions = array();
	
	/**
	 * list of user subscriptions
	 * @var	\wcf\data\paid\subscription\user\PaidSubscriptionUserList
	 */
	public $userSubscriptionList = array();
	
	/**
	 * @see	\wcf\page\AbstractPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get available subscriptions
		$this->subscriptions = PaidSubscriptionCacheBuilder::getInstance()->getData();
		
		// get user subscriptions
		$this->userSubscriptionList = new PaidSubscriptionUserList();
		$this->userSubscriptionList->getConditionBuilder()->add('userID = ?', array(WCF::getUser()->userID));
		$this->userSubscriptionList->getConditionBuilder()->add('isActive = ?', array(1));
		$this->userSubscriptionList->readObjects();
		
		foreach ($this->userSubscriptionList as $userSubscription) {
			if (isset($this->subscriptions[$userSubscription->subscriptionID])) {
				$userSubscription->setSubscription($this->subscriptions[$userSubscription->subscriptionID]);
				unset($this->subscriptions[$userSubscription->subscriptionID]);
			}
		}
		foreach ($this->userSubscriptionList as $userSubscription) {
			if ($userSubscription->getSubscription()->excludedSubscriptionIDs) {
				foreach (explode(',', $userSubscription->getSubscription()->excludedSubscriptionIDs) as $subscriptionID) {
					if (isset($this->subscriptions[$subscriptionID])) unset($this->subscriptions[$subscriptionID]);
				}	
			}
		}
	}
	
	/**
	 * @see	\wcf\page\AbstractPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'subscriptions' => $this->subscriptions,
			'userSubscriptions' => $this->userSubscriptionList
		));
	}
	
	/**
	 * @see	\wcf\page\Page::show()
	 */
	public function show() {
		// set active tab
		UserMenu::getInstance()->setActiveMenuItem('wcf.user.menu.settings.paidSubscription');
		
		parent::show();
	}
}
