<?php

namespace wcf\system\cache\builder;

use wcf\data\paid\subscription\PaidSubscriptionList;

/**
 * Caches the paid subscriptions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PaidSubscriptionCacheBuilder extends AbstractCacheBuilder
{
    #[\Override]
    protected function rebuild(array $parameters)
    {
        $subscriptionList = new PaidSubscriptionList();
        $subscriptionList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $subscriptionList->sqlOrderBy = 'showOrder';
        $subscriptionList->readObjects();
        $subscriptions = $subscriptionList->getObjects();

        // The subscriptions share one collection, loading the titles and
        // descriptions once stores them in the cache instead of querying them
        // on every request.
        if ($subscriptions !== []) {
            \reset($subscriptions)->getL10nValues('title');
        }

        return $subscriptions;
    }
}
