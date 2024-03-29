<?php

namespace wcf\system\box;

use wcf\data\paid\subscription\user\PaidSubscriptionUserList;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Box for paid subscriptions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class PaidSubscriptionsBoxController extends AbstractBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['contentTop', 'contentBottom', 'sidebarLeft', 'sidebarRight'];

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getLink('PaidSubscriptionList');
    }

    /**
     * @inheritDoc
     */
    public function hasLink()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function loadContent()
    {
        if (MODULE_PAID_SUBSCRIPTION) {
            // get available subscriptions
            $subscriptions = PaidSubscriptionCacheBuilder::getInstance()->getData();

            if (WCF::getUser()->userID) {
                // get purchased subscriptions
                $userSubscriptionList = new PaidSubscriptionUserList();
                $userSubscriptionList->getConditionBuilder()->add('userID = ?', [WCF::getUser()->userID]);
                $userSubscriptionList->getConditionBuilder()->add('isActive = ?', [1]);
                $userSubscriptionList->readObjects();

                // remove purchased subscriptions
                foreach ($userSubscriptionList as $userSubscription) {
                    if (isset($subscriptions[$userSubscription->subscriptionID])) {
                        $userSubscription->setSubscription($subscriptions[$userSubscription->subscriptionID]);
                        unset($subscriptions[$userSubscription->subscriptionID]);
                    }
                }
                // remove excluded subscriptions
                foreach ($userSubscriptionList as $userSubscription) {
                    if ($userSubscription->getSubscription()->excludedSubscriptionIDs) {
                        foreach (
                            \explode(
                                ',',
                                $userSubscription->getSubscription()->excludedSubscriptionIDs
                            ) as $subscriptionID
                        ) {
                            if (isset($subscriptions[$subscriptionID])) {
                                unset($subscriptions[$subscriptionID]);
                            }
                        }
                    }
                }
            }

            if (!empty($subscriptions)) {
                if ($this->getBox()->position == 'contentTop' || $this->getBox()->position == 'contentBottom') {
                    $templateName = 'boxPaidSubscriptions';
                } else {
                    $templateName = 'boxPaidSubscriptionsSidebar';
                }

                $this->content = WCF::getTPL()->fetch($templateName, 'wcf', ['subscriptions' => $subscriptions], true);
            }
        }
    }
}
