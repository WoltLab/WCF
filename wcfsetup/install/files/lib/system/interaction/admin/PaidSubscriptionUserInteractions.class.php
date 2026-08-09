<?php

namespace wcf\system\interaction\admin;

use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\event\interaction\admin\PaidSubscriptionUserInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for paid subscription users.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class PaidSubscriptionUserInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (
            \MODULE_PAID_SUBSCRIPTION === 0
            || !WCF::getSession()->getPermission('admin.paidSubscription.canManageSubscription')
        ) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction('core/paidSubscriptions/users/%s')
        ]);

        EventHandler::getInstance()->fire(
            new PaidSubscriptionUserInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return PaidSubscriptionUser::class;
    }
}
