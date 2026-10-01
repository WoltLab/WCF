<?php

namespace wcf\acp\form;

use wcf\acp\page\PaidSubscriptionListPage;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\http\Helper;
use wcf\system\interaction\admin\PaidSubscriptionInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the paid subscription edit form.
 *
 * @author  Marcel Werk, Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PaidSubscriptionEditForm extends PaidSubscriptionAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription.list';

    /**
     * @inheritDoc
     */
    public string $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(PaidSubscription::class);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new PaidSubscriptionInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(PaidSubscriptionListPage::class)
            ),
        ]);
    }

    #[\Override]
    protected function getAvailableSubscriptions(): array
    {
        return \array_filter(
            parent::getAvailableSubscriptions(),
            fn(int $key) => $key !== $this->formObject->getObjectID(),
            \ARRAY_FILTER_USE_KEY
        );
    }

    #[\Override]
    protected function getSubscriptionsByShowOrder(): array
    {
        return \array_filter(
            parent::getSubscriptionsByShowOrder(),
            fn(int $key) => $key !== $this->formObject->getObjectID(),
            \ARRAY_FILTER_USE_KEY
        );
    }
}
