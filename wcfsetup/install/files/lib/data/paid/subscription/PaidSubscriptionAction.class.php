<?php

namespace wcf\data\paid\subscription;

use wcf\command\paid\subscription\DisablePaidSubscription;
use wcf\command\paid\subscription\EnablePaidSubscription;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;

/**
 * Executes paid subscription-related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<PaidSubscription, PaidSubscriptionEditor>
 */
class PaidSubscriptionAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.paidSubscription.canManageSubscription'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.paidSubscription.canManageSubscription'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'toggle', 'update'];

    /**
     * @inheritDoc
     * @return  PaidSubscription
     */
    public function create()
    {
        $showOrder = 0;
        if (isset($this->parameters['data']['showOrder'])) {
            $showOrder = $this->parameters['data']['showOrder'];
            unset($this->parameters['data']['showOrder']);
        }

        /** @var PaidSubscription $subscription */
        $subscription = parent::create();
        $editor = new PaidSubscriptionEditor($subscription);
        $editor->setShowOrder($showOrder);

        return new PaidSubscription($subscription->subscriptionID);
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        parent::update();

        if (
            \count($this->objects) == 1
            && isset($this->parameters['data']['showOrder'])
            && $this->parameters['data']['showOrder'] != \reset($this->objects)->showOrder
        ) {
            \reset($this->objects)->setShowOrder($this->parameters['data']['showOrder']);
        }
    }

    /**
     * @deprecated 6.3
     */
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @deprecated 6.3 use the `EnablePaidSubscription` or `DisablePaidSubscription` commands instead.
     */
    public function toggle()
    {
        foreach ($this->objects as $editor) {
            if ($editor->isDisabled) {
                (new EnablePaidSubscription($editor->getDecoratedObject()))();
            } else {
                (new DisablePaidSubscription($editor->getDecoratedObject()))();
            }
        }
    }
}
