<?php
namespace wcf\system\cache\builder;
use wcf\data\paid\subscription\PaidSubscriptionList;

/**
 * Caches the paid subscriptions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class PaidSubscriptionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$subscriptionList = new PaidSubscriptionList();
		$subscriptionList->getConditionBuilder()->add('isDisabled = ?', [0]);
		$subscriptionList->sqlOrderBy = 'showOrder';
		$subscriptionList->readObjects();
		
		return $subscriptionList->getObjects();
	}
}
