<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\PaidSubscriptionUserEditForm;
use wcf\acp\form\UserEditForm;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserList;
use wcf\event\gridView\admin\PaidSubscriptionUserGridViewInitialized;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\PhraseColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\UserLinkColumnRenderer;
use wcf\system\interaction\admin\PaidSubscriptionUserInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\view\filter\UserFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of paid subscription users.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<PaidSubscriptionUser, PaidSubscriptionUserList>
 */
final class PaidSubscriptionUserGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('subscriptionUserID')
                ->label('wcf.global.objectID')
                ->sortable()
                ->renderer(new ObjectIdColumnRenderer()),
            GridViewColumn::for('userID')
                ->label('wcf.user.username')
                ->titleColumn()
                ->renderer(new UserLinkColumnRenderer(UserEditForm::class))
                ->filter(UserFilter::class)
                ->sortable(sortByDatabaseColumn: "user_table.username"),
            GridViewColumn::for('title')
                ->label('wcf.acp.paidSubscription.subscription')
                ->filter(new SelectFilter(
                    $this->getAvailableSubscriptions(),
                    'title',
                    'wcf.acp.paidSubscription.subscription',
                    'subscriptionID'
                ))
                ->renderer(new PhraseColumnRenderer())
                ->sortable(sortByDatabaseColumn: "paid_subscription.title"),
            GridViewColumn::for('endDate')
                ->label('wcf.acp.paidSubscription.user.endDate')
                ->renderer(new TimeColumnRenderer())
                ->filter(TimeFilter::class)
                ->sortable(),
        ]);

        $provider = new PaidSubscriptionUserInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(
                PaidSubscriptionUserEditForm::class,
                static fn(PaidSubscriptionUser $user) => $user->endDate > 0
            ),
        ]);
        $this->setInteractionProvider($provider);
        $this->setDefaultSortField("userID");
    }

    /**
     * @return array<int, string>
     */
    private function getAvailableSubscriptions(): array
    {
        $subscriptions = [];
        foreach (PaidSubscriptionCacheBuilder::getInstance()->getData() as $subscription) {
            \assert($subscription instanceof PaidSubscription);
            $subscriptions[$subscription->subscriptionID] = $subscription->getTitle();
        }

        return $subscriptions;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_PAID_SUBSCRIPTION
            && WCF::getSession()->getPermission('admin.paidSubscription.canManageSubscription');
    }

    #[\Override]
    protected function createObjectList(): PaidSubscriptionUserList
    {
        $list = new PaidSubscriptionUserList();
        $list->sqlSelects = "paid_subscription.title";
        $list->sqlJoins = "
            LEFT JOIN   wcf1_user user_table
            ON          user_table.userID = paid_subscription_user.userID
            LEFT JOIN   wcf1_paid_subscription paid_subscription
            ON          paid_subscription.subscriptionID = paid_subscription_user.subscriptionID";

        return $list;
    }

    #[\Override]
    protected function getInitializedEvent(): PaidSubscriptionUserGridViewInitialized
    {
        return new PaidSubscriptionUserGridViewInitialized($this);
    }
}
