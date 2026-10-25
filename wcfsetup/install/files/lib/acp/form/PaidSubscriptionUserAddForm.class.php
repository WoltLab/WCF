<?php

namespace wcf\acp\form;

use wcf\command\paid\subscription\user\CreatePaidSubscriptionUser;
use wcf\command\paid\subscription\user\ExtendPaidSubscriptionUser;
use wcf\command\paid\subscription\user\UpdatePaidSubscriptionUser;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserBuilder;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\http\Helper;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\user\UserFormField;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows the user subscription add form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<PaidSubscriptionUser, PaidSubscriptionUserBuilder>
 */
class PaidSubscriptionUserAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription.list';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_PAID_SUBSCRIPTION'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.paidSubscription.canManageSubscription'];

    /**
     * paid subscription the user is subscribed to
     */
    public PaidSubscription $subscription;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if ($this->formAction === 'create') {
            $this->subscription = Helper::fetchObjectFromQueryParameter(PaidSubscription::class);
        }
    }

    #[\Override]
    protected function getDatabaseObjectBuilder(): PaidSubscriptionUserBuilder
    {
        if ($this->formObject !== null) {
            return PaidSubscriptionUserBuilder::forUpdate($this->formObject);
        }

        return PaidSubscriptionUserBuilder::forCreate()
            ->setSubscription($this->subscription)
            ->setStartDate(\TIME_NOW);
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdatePaidSubscriptionUser($builder);
        }

        $subscriptionUser = PaidSubscriptionUser::getSubscriptionUser(
            $this->subscription->subscriptionID,
            (int)$builder->properties['userID']
        );
        if ($subscriptionUser === null) {
            return new CreatePaidSubscriptionUser($builder);
        }

        // The user is already subscribed, extend the existing subscription instead.
        $endDate = isset($builder->properties['endDate']) ? (int)$builder->properties['endDate'] : null;

        return static function () use ($subscriptionUser, $endDate): PaidSubscriptionUser {
            new ExtendPaidSubscriptionUser($subscriptionUser, $endDate)();

            return new PaidSubscriptionUser($subscriptionUser->subscriptionUserID);
        };
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $container = FormContainer::create('data');

        if ($this->formObject === null) {
            $container->appendChildren([
                UserFormField::create('user')
                    ->label('wcf.user.username')
                    ->required()
                    ->autoFocus()
                    ->saveValueCallback(static function (PaidSubscriptionUserBuilder $builder, UserFormField $field): void {
                        $builder->setUserID((int)$field->getSaveValue());
                    }),
            ]);
        }

        // Subscriptions without a length never expire, existing subscriptions
        // with an end date can only be edited if they expire.
        if ($this->formObject !== null || $this->subscription->subscriptionLength !== 0) {
            $endDateField = DateFormField::create('endDate')
                ->label('wcf.acp.paidSubscription.user.endDate')
                ->required()
                // The selected day ends the subscription at midnight UTC, the
                // current day would therefore result in an expired subscription.
                ->earliestDate(\TIME_NOW + 86400)
                ->saveValueCallback(static function (PaidSubscriptionUserBuilder $builder, DateFormField $field): void {
                    $builder->setEndDate((int)$field->getSaveValue());
                })
                ->loadValueCallback(static function (PaidSubscriptionUser $object, IFormField $field): void {
                    $field->value($object->endDate);
                });

            if ($this->formObject === null) {
                $endDateField->value($this->getDefaultEndDate());
            }

            $container->appendChildren([$endDateField]);
        }

        $this->form->appendChildren([$container]);
    }

    /**
     * Returns the end date of a subscription that starts now.
     */
    protected function getDefaultEndDate(): int
    {
        $d = DateUtil::getDateTimeByTimestamp(\TIME_NOW);
        $d->add($this->subscription->getDateInterval());

        return $d->getTimestamp();
    }

    #[\Override]
    protected function setFormAction(): void
    {
        if ($this->formObject !== null) {
            parent::setFormAction();

            return;
        }

        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, [
            'id' => $this->subscription->subscriptionID,
        ]));
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'subscription' => $this->subscription,
        ]);
    }
}
