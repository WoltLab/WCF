<?php
namespace wcf\system\cache\builder;
use wcf\data\paid\subscription\PaidSubscriptionList;

/**
 * Caches the paid subscriptions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class PaidSubscriptionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	protected function rebuild(array $parameters) {
		$subscriptionList = new PaidSubscriptionList();
		$subscriptionList->getConditionBuilder()->add('isDisabled = ?', array(0));
		$subscriptionList->sqlOrderBy = 'showOrder';
		$subscriptionList->readObjects();
		
		return $subscriptionList->getObjects();
	}
}
