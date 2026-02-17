<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserEditForm;
use wcf\acp\page\PaidSubscriptionTransactionLogPage;
use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLog;
use wcf\data\paid\subscription\transaction\log\PaidSubscriptionTransactionLogList;
use wcf\event\gridView\admin\PaidSubscriptionTransactionLogGridViewInitialized;
use wcf\system\cache\builder\PaidSubscriptionCacheBuilder;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ILinkColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\TruncatedTextColumnRenderer;
use wcf\system\gridView\renderer\UserColumnRenderer;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\IInteractionProvider;
use wcf\system\interaction\LinkInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\view\filter\UserFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of paid subscription transaction logs.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<PaidSubscriptionTransactionLog, PaidSubscriptionTransactionLogList>
 */
final class PaidSubscriptionTransactionLogGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('logID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('logMessage')
                ->label('wcf.acp.paidSubscription.transactionLog.logMessage')
                ->titleColumn()
                ->filter(TextFilter::class)
                ->unsafeDisableEncoding()
                ->renderer(new TruncatedTextColumnRenderer())
                ->sortable(),
            GridViewColumn::for('userID')
                ->label('wcf.user.username')
                ->sortable(sortByDatabaseColumn: 'user_table.username')
                ->renderer(
                    new class extends UserColumnRenderer implements ILinkColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof PaidSubscriptionTransactionLog);

                            if (!WCF::getSession()->getPermission('admin.user.canEditUser')) {
                                return parent::render($value, $row);
                            } else {
                                $user = UserRuntimeCache::getInstance()->getObject($value);
                                if ($user === null) {
                                    return '';
                                }

                                return \sprintf(
                                    '<a href="%s">%s</a>',
                                    LinkHandler::getInstance()->getControllerLink(UserEditForm::class, [
                                        'id' => $user->userID,
                                    ]),
                                    StringUtil::encodeHTML($user->username)
                                );
                            }
                        }
                    }
                )
                ->filter(UserFilter::class),
            GridViewColumn::for('subscriptionID')
                ->label('wcf.acp.paidSubscription.subscription')
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof PaidSubscriptionTransactionLog);

                            if (!$row->subscriptionID) {
                                return '';
                            }

                            $subscription = PaidSubscriptionCacheBuilder::getInstance()->getData()[$row->subscriptionID];
                            return StringUtil::encodeHTML($subscription->getTitle());
                        }
                    }
                ),
            GridViewColumn::for('paymentMethodObjectTypeID')
                ->label('wcf.acp.paidSubscription.transactionLog.paymentMethod')
                ->filter(new SelectFilter(
                    $this->getAvailablePaymentMethods(),
                    'paymentMethodObjectTypeID',
                    'wcf.acp.paidSubscription.transactionLog.paymentMethod'
                ))
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof PaidSubscriptionTransactionLog);

                            return WCF::getLanguage()->get('wcf.payment.' . $row->getPaymentMethodName());
                        }
                    }
                )
                ->sortable(),
            GridViewColumn::for('transactionID')
                ->label('wcf.acp.paidSubscription.transactionLog.transactionID')
                ->renderer(new DefaultColumnRenderer())
                ->filter(TextFilter::class)
                ->sortable(),
            GridViewColumn::for('logTime')
                ->label('wcf.acp.paidSubscription.transactionLog.logTime')
                ->renderer(new TimeColumnRenderer())
                ->filter(TimeFilter::class)
                ->sortable(),
        ]);

        $this->addAvailableFilters([
            new SelectFilter(
                $this->getAvailableSubscriptions(),
                'subscriptionID',
                'wcf.acp.paidSubscription.subscription'
            )
        ]);
        $this->setInteractionProvider($this->getInteractions());
        $this->addRowLink(new GridViewRowLink(PaidSubscriptionTransactionLogPage::class));
        $this->setDefaultSortField("logTime");
        $this->setDefaultSortOrder("DESC");
    }

    /**
     * @return array<int, string>
     */
    private function getAvailablePaymentMethods(): array
    {
        $paymentMethods = [];
        foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.payment.method') as $objectType) {
            $paymentMethods[$objectType->objectTypeID] = 'wcf.payment.' . $objectType->objectType;
        }

        return $paymentMethods;
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
    protected function createObjectList(): PaidSubscriptionTransactionLogList
    {
        $list = new PaidSubscriptionTransactionLogList();
        $list->sqlJoins = "
            LEFT JOIN   wcf1_user user_table
            ON          user_table.userID = paid_subscription_transaction_log.userID";

        return $list;
    }

    #[\Override]
    protected function getInitializedEvent(): PaidSubscriptionTransactionLogGridViewInitialized
    {
        return new PaidSubscriptionTransactionLogGridViewInitialized($this);
    }

    private function getInteractions(): IInteractionProvider
    {
        return new class extends AbstractInteractionProvider {
            public function __construct()
            {
                $this->addInteractions([
                    new LinkInteraction(
                        'showDetails',
                        PaidSubscriptionTransactionLogPage::class,
                        'wcf.acp.paidSubscription.transactionLog.showTransactionDetails'
                    ),
                ]);
            }

            #[\Override]
            public function getObjectClassName(): string
            {
                return PaidSubscriptionTransactionLog::class;
            }
        };
    }
}
