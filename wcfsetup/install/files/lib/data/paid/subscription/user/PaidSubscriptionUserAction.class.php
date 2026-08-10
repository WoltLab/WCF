<?php

namespace wcf\data\paid\subscription\user;

use wcf\command\paid\subscription\AddGroupMembership;
use wcf\command\paid\subscription\RemoveGroupMembership;
use wcf\command\paid\subscription\user\ExtendPaidSubscriptionUser;
use wcf\command\paid\subscription\user\RestorePaidSubscriptionUser;
use wcf\command\paid\subscription\user\RevokePaidSubscriptionUser;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\user\User;
use wcf\system\exception\UserInputException;
use wcf\util\DateUtil;

/**
 * Executes paid subscription user-related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<PaidSubscriptionUser, PaidSubscriptionUserEditor>
 */
class PaidSubscriptionUserAction extends AbstractDatabaseObjectAction
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
    protected $requireACP = ['create', 'delete', 'update'];

    #[\Override]
    public function create()
    {
        $this->parameters['data']['subscriptionID'] = $this->parameters['subscription']->subscriptionID;
        $this->parameters['data']['userID'] = $this->parameters['user']->userID;
        if (!isset($this->parameters['data']['startDate'])) {
            $this->parameters['data']['startDate'] = \TIME_NOW;
        }
        if (!isset($this->parameters['data']['endDate'])) {
            if (!$this->parameters['subscription']->subscriptionLength) {
                $this->parameters['data']['endDate'] = 0;
            } else {
                $d = DateUtil::getDateTimeByTimestamp($this->parameters['data']['startDate']);
                $d->add($this->parameters['subscription']->getDateInterval());
                $this->parameters['data']['endDate'] = $d->getTimestamp();
            }
        }
        if (!isset($this->parameters['data']['isActive'])) {
            $this->parameters['data']['isActive'] = 1;
        }

        $subscriptionUser = parent::create();

        new AddGroupMembership(
            $this->parameters['subscription'],
            $this->parameters['user']
        )();

        return $subscriptionUser;
    }

    #[\Override]
    public function validateCreate()
    {
        parent::validateCreate();

        if (
            !isset($this->parameters['subscription'])
            || !($this->parameters['subscription'] instanceof PaidSubscription)
        ) {
            throw new UserInputException('subscription');
        }
        if (!isset($this->parameters['user']) || !($this->parameters['user'] instanceof User)) {
            throw new UserInputException('user');
        }
    }

    /**
     * Extends an existing subscription.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `ExtendPaidSubscriptionUser` command instead.
     */
    public function extend()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $editor) {
            if (isset($this->parameters['data']['endDate'])) {
                new ExtendPaidSubscriptionUser(
                    $editor->getDecoratedObject(),
                    $this->parameters['data']['endDate']
                )();
            } else {
                new ExtendPaidSubscriptionUser($editor->getDecoratedObject())();
            }
        }
    }

    #[\Override]
    public function delete()
    {
        $this->revoke();

        return parent::delete();
    }

    /**
     * Revokes an existing subscription.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `RevokePaidSubscriptionUser` command instead.
     */
    public function revoke()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $editor) {
            new RevokePaidSubscriptionUser($editor->getDecoratedObject())();
        }
    }

    /**
     * Validates the revoke action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateRevoke()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $subscriptionUser) {
            if ($subscriptionUser->isActive === 0) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Restores an existing subscription.
     *
     * @return void
     *
     * @deprecated 6.3 use the `RestorePaidSubscriptionUser` command instead.
     */
    public function restore()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $editor) {
            new RestorePaidSubscriptionUser($editor->getDecoratedObject())();
        }
    }

    /**
     * Validates the restore action.
     *
     * @return void
     *
     * @deprecated 6.3
     */
    public function validateRestore()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $subscriptionUser) {
            if ($subscriptionUser->isActive !== 0) {
                throw new UserInputException('objectIDs');
            }
        }
    }

    /**
     * Applies group memberships.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `AddGroupMembership` command instead.
     */
    public function addGroupMemberships()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $subscriptionUser) {
            new AddGroupMembership(
                $subscriptionUser->getSubscription(),
                $subscriptionUser->getUser()
            )();
        }
    }

    /**
     * Removes group memberships.
     *
     * @return void
     *
     * @deprecated 6.3 Use the `RemoveGroupMembership` command instead.
     */
    public function removeGroupMemberships()
    {
        if ($this->objects === []) {
            $this->readObjects();
        }

        foreach ($this->getObjects() as $editor) {
            new RemoveGroupMembership($editor->getSubscription(), $editor->getUser())();
        }
    }
}
