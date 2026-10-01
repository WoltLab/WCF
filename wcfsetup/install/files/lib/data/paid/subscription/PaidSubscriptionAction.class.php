<?php

namespace wcf\data\paid\subscription;

use wcf\command\paid\subscription\DeletePaidSubscription;
use wcf\command\paid\subscription\DisablePaidSubscription;
use wcf\command\paid\subscription\EnablePaidSubscription;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;

/**
 * Executes paid subscription-related actions.
 *
 * Paid subscriptions should be created, updated and deleted through the
 * `CreatePaidSubscription`, `UpdatePaidSubscription` and
 * `DeletePaidSubscription` commands, the `create`, `update` and `delete`
 * actions are `@deprecated 6.3`.
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
     * @deprecated 6.3 use the `DeletePaidSubscription` command instead.
     */
    #[\Override]
    public function delete()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->objects as $object) {
            new DeletePaidSubscription($object->getDecoratedObject())();
        }

        return \count($this->objects);
    }

    /**
     * @deprecated 6.3
     */
    #[\Override]
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @deprecated 6.3 use the `EnablePaidSubscription` or `DisablePaidSubscription` commands instead.
     */
    #[\Override]
    public function toggle()
    {
        foreach ($this->objects as $editor) {
            if ($editor->isDisabled !== 0) {
                new EnablePaidSubscription($editor->getDecoratedObject())();
            } else {
                new DisablePaidSubscription($editor->getDecoratedObject())();
            }
        }
    }
}
