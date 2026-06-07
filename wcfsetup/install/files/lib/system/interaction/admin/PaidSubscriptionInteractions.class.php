<?php

namespace wcf\system\interaction\admin;

use wcf\acp\form\PaidSubscriptionUserAddForm;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\event\interaction\admin\PaidSubscriptionInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\LinkInteraction;

/**
 * Interaction provider for paid subscriptions.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class PaidSubscriptionInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new LinkInteraction(
                'add-user',
                PaidSubscriptionUserAddForm::class,
                'wcf.acp.paidSubscription.user.add'
            ),
            new DeleteInteraction("core/paidSubscriptions/%s"),
        ]);

        EventHandler::getInstance()->fire(
            new PaidSubscriptionInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return PaidSubscription::class;
    }
}
