<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\data\paid\subscription\user\PaidSubscriptionUserList;
use wcf\page\IPage;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\WCF;

/**
 * Dashboard box for paid subscriptions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
class PaidSubscriptionsDashboardBox extends AbstractContentDashboardBox {
	/**
	 * list of available paid subscriptions
	 * @var	array
	 */
	public $subscriptions = array();
	
	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		parent::init($box, $page);
		
		if (WCF::getUser()->userID && MODULE_PAID_SUBSCRIPTION) {
			// get available subscriptions
			$this->subscriptions = PaidSubscriptionCacheBuilder::getInstance()->getData();
			
			// get purchased subscriptions
			$userSubscriptionList = new PaidSubscriptionUserList();
			$userSubscriptionList->getConditionBuilder()->add('userID = ?', array(WCF::getUser()->userID));
			$userSubscriptionList->getConditionBuilder()->add('isActive = ?', array(1));
			$userSubscriptionList->readObjects();
			
			// remove purchased subscriptions
			foreach ($userSubscriptionList as $userSubscription) {
				if (isset($this->subscriptions[$userSubscription->subscriptionID])) {
					$userSubscription->setSubscription($this->subscriptions[$userSubscription->subscriptionID]);
					unset($this->subscriptions[$userSubscription->subscriptionID]);
				}
			}
			// remove excluded subscriptions
			foreach ($userSubscriptionList as $userSubscription) {
				if ($userSubscription->getSubscription()->excludedSubscriptionIDs) {
					foreach (explode(',', $userSubscription->getSubscription()->excludedSubscriptionIDs) as $subscriptionID) {
						if (isset($this->subscriptions[$subscriptionID])) unset($this->subscriptions[$subscriptionID]);
					}
				}
			}
		}
		$this->fetched();
	}
	
	/**
	 * @see	\wcf\system\dashboard\box\AbstractContentDashboardBox::render()
	 */
	protected function render() {
		if (!empty($this->subscriptions)) {
			WCF::getTPL()->assign(array(
				'subscriptions' => $this->subscriptions
			));
			
			return WCF::getTPL()->fetch('dashboardBoxPaidSubscriptions');
		}
		
		return '';
	}
}
