<?php

namespace wcf\acp\form;

use wcf\command\paid\subscription\CreatePaidSubscription;
use wcf\command\paid\subscription\UpdatePaidSubscription;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\paid\subscription\L10nPaidSubscriptionList;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\PaidSubscriptionBuilder;
use wcf\data\user\group\UserGroup;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\exception\NamedUserException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\SuffixFormFieldContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\EmptyFormFieldDependency;
use wcf\system\form\builder\field\FloatFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\wysiwyg\WysiwygFormField;
use wcf\system\payment\method\PaymentMethodHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HtmlString;
use wcf\util\StringUtil;

/**
 * Shows the paid subscription add form.
 *
 * @author  Marcel Werk, Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<PaidSubscription, PaidSubscriptionBuilder>
 */
class PaidSubscriptionAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.paidSubscription.add';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_PAID_SUBSCRIPTION'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.paidSubscription.canManageSubscription'];

    /**
     * @inheritDoc
     */
    public string $objectEditLinkController = PaidSubscriptionEditForm::class;

    /**
     * Maximum subscription length per unit.
     */
    private const MAXIMUM_LENGTH = [
        'D' => 90,
        'M' => 24,
        'Y' => 5,
    ];

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (PaymentMethodHandler::getInstance()->getPaymentMethods() === []) {
            throw new NamedUserException(HtmlString::fromSafeHtml(
                WCF::getLanguage()->get('wcf.acp.paidSubscription.error.noPaymentMethods')
            ));
        }
    }

    #[\Override]
    protected function getDatabaseObjectBuilder(): PaidSubscriptionBuilder
    {
        if ($this->formObject !== null) {
            return PaidSubscriptionBuilder::forUpdate($this->formObject);
        }

        return PaidSubscriptionBuilder::forCreate();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdatePaidSubscription($builder);
        }

        return new CreatePaidSubscription($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $canChangePaymentOptions = $this->formObject === null || !$this->formObject->hasActiveSubscriptions();
        $availableSubscriptions = $this->getAvailableSubscriptions();
        $availableUserGroups = $this->getAvailableUserGroups();
        $availableCurrencies = $this->getAvailableCurrencies();

        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    TitleFormField::create()
                        ->l10n()
                        ->required()
                        ->autoFocus()
                        ->maximumLength(255)
                        ->saveValueCallback(static function (PaidSubscriptionBuilder $builder, TitleFormField $field): void {
                            $builder->setTitle($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (PaidSubscription $object, IFormField $field): void {
                            $field->value($object->getL10nValues('title'));
                        }),
                    WysiwygFormField::create('description')
                        ->label('wcf.global.description')
                        ->objectType('com.woltlab.wcf.paidSubscription')
                        ->l10n()
                        ->saveValueCallback(static function (PaidSubscriptionBuilder $builder, WysiwygFormField $field): void {
                            $builder->setDescription($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (PaidSubscription $object, IFormField $field): void {
                            $field->value($object->getL10nValues('description'));
                        }),
                    ShowOrderFormField::create()
                        ->description('wcf.acp.paidSubscription.showOrder.description')
                        ->options($this->getSubscriptionsByShowOrder(), labelLanguageItems: false)
                        ->saveValueCallback(static function (PaidSubscriptionBuilder $builder, ShowOrderFormField $field): void {
                            $showOrder = $field->getSaveValue();
                            if ($showOrder !== null) {
                                $builder->setShowOrder($showOrder);
                            }
                        })
                        ->loadValueCallback(static function (PaidSubscription $object, IFormField $field): void {
                            $field->value($object->showOrder);
                        }),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.paidSubscription.isDisabled')
                        ->description('wcf.acp.paidSubscription.isDisabled.description')
                        ->saveValueCallback(static function (PaidSubscriptionBuilder $builder, BooleanFormField $field): void {
                            $builder->setIsDisabled((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (PaidSubscription $object, IFormField $field): void {
                            $field->value($object->isDisabled);
                        }),
                    MultipleSelectionFormField::create('excludedSubscriptionIDs')
                        ->label('wcf.acp.paidSubscription.excludedSubscriptions')
                        ->description('wcf.acp.paidSubscription.excludedSubscriptions.description')
                        ->available($availableSubscriptions !== [])
                        ->options($availableSubscriptions, labelLanguageItems: false)
                        ->saveValueCallback(static function (PaidSubscriptionBuilder $builder, MultipleSelectionFormField $field): void {
                            $builder->setExcludedSubscriptionIDs(\array_values(ArrayUtil::toIntegerArray($field->getValue() ?? [])));
                        })
                        ->loadValueCallback(static function (PaidSubscription $object, IFormField $field) use ($availableSubscriptions): void {
                            // Deleted subscriptions were never removed from the list.
                            $field->value(\array_values(\array_intersect(
                                self::explodeIDs($object->excludedSubscriptionIDs),
                                \array_keys($availableSubscriptions)
                            )));
                        }),
                ]),
            FormContainer::create('paymentOptions')
                ->label('wcf.acp.paidSubscription.paymentOptions')
                ->appendChildren([
                    SuffixFormFieldContainer::create('costContainer')
                        ->label('wcf.acp.paidSubscription.cost')
                        ->field(
                            FloatFormField::create('cost')
                                ->required()
                                ->minimum(0.01)
                                ->step(0.01)
                                ->immutable(!$canChangePaymentOptions)
                                ->saveValueCallback(static function (PaidSubscriptionBuilder $builder, FloatFormField $field): void {
                                    // The payment options of subscriptions with active
                                    // subscribers cannot be changed.
                                    if ($field->isImmutable()) {
                                        return;
                                    }

                                    $currency = $field->getDocument()->getNodeById('currency');
                                    \assert($currency instanceof SingleSelectionFormField);

                                    $builder->setPrice((float)$field->getSaveValue(), $currency->getSaveValue());
                                })
                                ->loadValueCallback(static function (PaidSubscription $object, IFormField $field): void {
                                    $field->value((float)$object->cost);
                                })
                        )
                        ->suffixField(
                            SingleSelectionFormField::create('currency')
                                ->required()
                                ->options(\array_combine($availableCurrencies, $availableCurrencies), labelLanguageItems: false)
                                ->value(\in_array('USD', $availableCurrencies, true) ? 'USD' : \reset($availableCurrencies))
                                ->immutable(!$canChangePaymentOptions)
                                ->loadValueCallback(static function (PaidSubscription $object, IFormField $field) use ($availableCurrencies): void {
                                    // The payment methods may no longer offer the stored currency.
                                    if (\in_array($object->currency, $availableCurrencies, true)) {
                                        $field->value($object->currency);
                                    }
                                })
                        ),
                    BooleanFormField::create('subscriptionLengthPermanent')
                        ->label('wcf.acp.paidSubscription.subscriptionLength.permanent')
                        ->immutable(!$canChangePaymentOptions)
                        ->saveValueCallback(static function (PaidSubscriptionBuilder $builder, BooleanFormField $field): void {
                            if ($field->isImmutable()) {
                                return;
                            }

                            if ($field->getSaveValue() === 1) {
                                $builder->setPermanent();
                            } else {
                                $document = $field->getDocument();
                                $length = $document->getNodeById('subscriptionLength');
                                \assert($length instanceof IntegerFormField);
                                $unit = $document->getNodeById('subscriptionLengthUnit');
                                \assert($unit instanceof SingleSelectionFormField);
                                $isRecurring = $document->getNodeById('isRecurring');
                                \assert($isRecurring instanceof BooleanFormField);

                                $builder->setLength(
                                    $length->getSaveValue(),
                                    $unit->getSaveValue(),
                                    (bool)$isRecurring->getSaveValue()
                                );
                            }
                        })
                        ->loadValueCallback(static function (PaidSubscription $object, IFormField $field): void {
                            $field->value($object->subscriptionLength === 0);
                        }),
                    SuffixFormFieldContainer::create('subscriptionLengthContainer')
                        ->label('wcf.acp.paidSubscription.subscriptionLength')
                        ->addDependency(
                            EmptyFormFieldDependency::create('subscriptionLengthPermanent')
                                ->fieldId('subscriptionLengthPermanent')
                        )
                        ->field(
                            IntegerFormField::create('subscriptionLength')
                                ->required()
                                ->minimum(1)
                                ->value(1)
                                ->immutable(!$canChangePaymentOptions)
                                ->addValidator(new FormFieldValidator(
                                    'maximumLength',
                                    static function (IntegerFormField $field): void {
                                        $unit = $field->getDocument()->getNodeById('subscriptionLengthUnit');
                                        \assert($unit instanceof SingleSelectionFormField);

                                        $maximumLength = self::MAXIMUM_LENGTH[(string)$unit->getValue()] ?? null;
                                        if ($maximumLength !== null && $field->getValue() > $maximumLength) {
                                            $field->addValidationError(new FormFieldValidationError(
                                                'invalid',
                                                'wcf.acp.paidSubscription.subscriptionLength.error.invalid'
                                            ));
                                        }
                                    }
                                ))
                                ->loadValueCallback(static function (PaidSubscription $object, IFormField $field): void {
                                    if ($object->subscriptionLength !== 0) {
                                        $field->value($object->subscriptionLength);
                                    }
                                })
                        )
                        ->suffixField(
                            SingleSelectionFormField::create('subscriptionLengthUnit')
                                ->required()
                                ->options([
                                    'D' => 'wcf.acp.paidSubscription.subscriptionLengthUnit.D',
                                    'M' => 'wcf.acp.paidSubscription.subscriptionLengthUnit.M',
                                    'Y' => 'wcf.acp.paidSubscription.subscriptionLengthUnit.Y',
                                ])
                                ->value('D')
                                ->immutable(!$canChangePaymentOptions)
                                ->loadValueCallback(static function (PaidSubscription $object, IFormField $field): void {
                                    if ($object->subscriptionLengthUnit !== '') {
                                        $field->value($object->subscriptionLengthUnit);
                                    }
                                })
                        ),
                    BooleanFormField::create('isRecurring')
                        ->label('wcf.acp.paidSubscription.isRecurring')
                        ->description('wcf.acp.paidSubscription.isRecurring.description')
                        ->immutable(!$canChangePaymentOptions)
                        ->addDependency(
                            EmptyFormFieldDependency::create('subscriptionLengthPermanent')
                                ->fieldId('subscriptionLengthPermanent')
                        )
                        ->loadValueCallback(static function (PaidSubscription $object, IFormField $field): void {
                            $field->value($object->isRecurring);
                        }),
                    MultipleSelectionFormField::create('groupIDs')
                        ->label('wcf.acp.paidSubscription.userGroups')
                        ->description('wcf.acp.paidSubscription.userGroups.description')
                        ->required()
                        ->options($availableUserGroups, labelLanguageItems: false)
                        ->saveValueCallback(static function (PaidSubscriptionBuilder $builder, MultipleSelectionFormField $field): void {
                            $builder->setGroupIDs(\array_values(ArrayUtil::toIntegerArray($field->getValue() ?? [])));
                        })
                        ->loadValueCallback(static function (PaidSubscription $object, IFormField $field) use ($availableUserGroups): void {
                            $field->value(\array_values(\array_intersect(
                                self::explodeIDs($object->groupIDs),
                                \array_keys($availableUserGroups)
                            )));
                        }),
                ]),
        ]);

        if ($this->formObject === null) {
            // New subscriptions are appended by default.
            $showOrder = $this->form->getNodeById('showOrder');
            \assert($showOrder instanceof ShowOrderFormField);
            $showOrder->value(\count($showOrder->getOptions()) - 1);
        }
    }

    /**
     * Returns the titles of the subscriptions that can be excluded, sorted by
     * their title.
     *
     * @return array<int, string>
     */
    protected function getAvailableSubscriptions(): array
    {
        $subscriptionList = new L10nPaidSubscriptionList();
        $subscriptionList->sqlOrderBy = 'title';
        $subscriptionList->readObjects();

        // The labels of selection options are printed as HTML.
        return \array_map(
            static fn(PaidSubscription $subscription) => StringUtil::encodeHTML($subscription->getTitle()),
            $subscriptionList->getObjects()
        );
    }

    /**
     * Returns the titles of the other subscriptions, sorted by their position.
     *
     * @return array<int, string>
     */
    protected function getSubscriptionsByShowOrder(): array
    {
        $subscriptionList = new L10nPaidSubscriptionList();
        $subscriptionList->sqlOrderBy = 'showOrder, subscriptionID';
        $subscriptionList->readObjects();

        return \array_map(
            static fn(PaidSubscription $subscription) => StringUtil::encodeHTML($subscription->getTitle()),
            $subscriptionList->getObjects()
        );
    }

    /**
     * @return array<int, string>
     */
    private function getAvailableUserGroups(): array
    {
        $userGroups = UserGroup::getSortedAccessibleGroups(
            [],
            [UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]
        );

        return \array_map(
            static fn(UserGroup $userGroup) => StringUtil::encodeHTML($userGroup->getTitle()),
            $userGroups
        );
    }

    /**
     * @return list<string>
     */
    private function getAvailableCurrencies(): array
    {
        $availableCurrencies = [];
        foreach (PaymentMethodHandler::getInstance()->getPaymentMethods() as $paymentMethod) {
            $availableCurrencies = [...$availableCurrencies, ...$paymentMethod->getSupportedCurrencies()];
        }
        $availableCurrencies = \array_values(\array_unique($availableCurrencies));
        \sort($availableCurrencies);

        return $availableCurrencies;
    }

    /**
     * @return list<int>
     */
    private static function explodeIDs(?string $ids): array
    {
        if ($ids === null || $ids === '') {
            return [];
        }

        return \array_values(ArrayUtil::toIntegerArray(\explode(',', $ids)));
    }
}
