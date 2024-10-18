<?php

namespace wcf\data\paid\subscription;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit paid subscriptions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static PaidSubscription    create(array $parameters = [])
 * @method      PaidSubscription    getDecoratedObject()
 * @mixin       PaidSubscription
 */
class PaidSubscriptionEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = PaidSubscription::class;

    /**
     * Sets the show order of the subscription.
     *
     * @param int $showOrder
     */
    public function setShowOrder($showOrder = 0)
    {
        $sql = "SELECT  MAX(showOrder)
                FROM    wcf1_paid_subscription";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $maxShowOrder = $statement->fetchSingleColumn();
        if (!$maxShowOrder) {
            $maxShowOrder = 0;
        }

        if (!$showOrder || $showOrder > $maxShowOrder) {
            $newShowOrder = $maxShowOrder + 1;
        } else {
            // shift other subscriptions
            $sql = "UPDATE  wcf1_paid_subscription
                    SET     showOrder = showOrder + 1
                    WHERE   showOrder >= ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $showOrder,
            ]);

            $newShowOrder = $showOrder;
        }

        $this->update(['showOrder' => $newShowOrder]);
    }

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        PaidSubscriptionCacheBuilder::getInstance()->reset();
    }
}
