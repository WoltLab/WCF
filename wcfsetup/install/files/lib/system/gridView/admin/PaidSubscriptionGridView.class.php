<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\PaidSubscriptionEditForm;
use wcf\data\DatabaseObject;
use wcf\data\paid\subscription\I18nPaidSubscriptionList;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\event\gridView\admin\PaidSubscriptionGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\PhraseColumnRenderer;
use wcf\system\interaction\admin\PaidSubscriptionInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\payment\method\PaymentMethodHandler;
use wcf\system\view\filter\FloatFilter;
use wcf\system\view\filter\I18nTextFilter;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\ObjectIdFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of paid subscriptions.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<PaidSubscription, I18nPaidSubscriptionList>
 */
final class PaidSubscriptionGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('subscriptionID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(ObjectIdFilter::class)
                ->sortable(),
            GridViewColumn::for('title')
                ->label('wcf.global.title')
                ->titleColumn()
                ->renderer(new PhraseColumnRenderer())
                ->filter(I18nTextFilter::class)
                ->sortable(sortByDatabaseColumn: 'titleI18n'),
            GridViewColumn::for('cost')
                ->label('wcf.acp.paidSubscription.cost')
                ->sortable()
                ->filter(FloatFilter::class)
                ->renderer(
                    new class extends NumberColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof PaidSubscription);
                            $locale = WCF::getLanguage()->getLocale();

                            return \NumberFormatter::create($locale, \NumberFormatter::CURRENCY)
                                ->formatCurrency($row->cost, $row->currency);
                        }
                    }
                ),
            GridViewColumn::for('subscriptionLength')
                ->label('wcf.acp.paidSubscription.subscriptionLength')
                ->sortable()
                ->renderer(
                    new class extends NumberColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof PaidSubscription);
                            if (!$row->subscriptionLength) {
                                return '&infin;';
                            }

                            return \sprintf(
                                "%s %d",
                                WCF::getLanguage()->get(
                                    "wcf.acp.paidSubscription.subscriptionLengthUnit." . $row->subscriptionLengthUnit
                                ),
                                $row->subscriptionLength
                            );
                        }
                    }
                ),
            GridViewColumn::for('showOrder')
                ->label('wcf.global.showOrder')
                ->sortable()
                ->renderer(new NumberColumnRenderer())
                ->filter(IntegerFilter::class)
        ]);

        $this->addAvailableFilters([
            new I18nTextFilter('description', 'wcf.global.description'),
            new SelectFilter($this->getAvailableCurrencies(), 'currency', 'wcf.acp.paidSubscription.currency'),
        ]);
        $provider = new PaidSubscriptionInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(PaidSubscriptionEditForm::class)
        ]);
        $this->setInteractionProvider($provider);
        $this->addQuickInteraction(
            new ToggleInteraction(
                "enable",
                "core/paid/subscriptions/%s/enable",
                "core/paid/subscriptions/%s/disable"
            )
        );

        $this->setDefaultSortField('showOrder');
        $this->addRowLink(new GridViewRowLink(PaidSubscriptionEditForm::class));
    }

    /**
     * @return string[]
     */
    private function getAvailableCurrencies(): array
    {
        $availableCurrencies = [];
        foreach (PaymentMethodHandler::getInstance()->getPaymentMethods() as $paymentMethod) {
            $availableCurrencies = \array_merge(
                $availableCurrencies,
                $paymentMethod->getSupportedCurrencies()
            );
        }

        $availableCurrencies = \array_unique($availableCurrencies);
        \sort($availableCurrencies);

        return \array_combine($availableCurrencies, $availableCurrencies);
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_PAID_SUBSCRIPTION
            && WCF::getSession()->getPermission('admin.paidSubscription.canManageSubscription');
    }

    #[\Override]
    protected function createObjectList(): I18nPaidSubscriptionList
    {
        return new I18nPaidSubscriptionList();
    }

    #[\Override]
    protected function getInitializedEvent(): PaidSubscriptionGridViewInitialized
    {
        return new PaidSubscriptionGridViewInitialized($this);
    }
}
