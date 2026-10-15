<?php

namespace wcf\acp\form;

use wcf\command\trophy\CreateTrophy;
use wcf\command\trophy\UpdateTrophy;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyBuilder;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\exception\NamedUserException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\FileProcessorFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\ObjectFilterFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\trophy\TrophyBadgeFormField;
use wcf\system\object\filter\builder\TrophyObjectFilterBuilder;
use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Represents the trophy add form.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<Trophy, TrophyBuilder>
 */
class TrophyAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.trophy.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.trophy.canManageTrophy'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_TROPHY'];

    /**
     * @inheritDoc
     */
    public string $objectEditLinkController = TrophyEditForm::class;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (TrophyCategoryCache::getInstance()->getCategories() === []) {
            throw new NamedUserException(HtmlString::fromSafeHtml(
                WCF::getLanguage()->getDynamicVariable('wcf.acp.trophy.error.noCategories')
            ));
        }
    }

    #[\Override]
    protected function getDatabaseObjectBuilder(): TrophyBuilder
    {
        if ($this->formObject !== null) {
            return TrophyBuilder::forUpdate($this->formObject);
        }

        return TrophyBuilder::forCreate();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateTrophy($builder);
        }

        return new CreateTrophy($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    TitleFormField::create()
                        ->l10n()
                        ->required()
                        ->autoFocus()
                        ->maximumLength(255)
                        ->saveValueCallback(static function (TrophyBuilder $builder, TitleFormField $field): void {
                            $builder->setTitle($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (Trophy $object, IFormField $field): void {
                            $field->value($object->getL10nValues('title'));
                        }),
                    MultilineTextFormField::create('description')
                        ->label('wcf.acp.trophy.description')
                        ->l10n()
                        ->saveValueCallback(static function (TrophyBuilder $builder, MultilineTextFormField $field): void {
                            $builder->setDescription($field->getL10nValues());
                        })
                        ->loadValueCallback(static function (Trophy $object, IFormField $field): void {
                            $field->value($object->getL10nValues('description'));
                        }),
                    BooleanFormField::create('trophyUseHtml')
                        ->label('wcf.acp.trophy.trophyUseHtml')
                        ->saveValueCallback(static function (TrophyBuilder $builder, BooleanFormField $field): void {
                            $builder->setTrophyUseHtml((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Trophy $object, IFormField $field): void {
                            $field->value($object->trophyUseHtml);
                        }),
                    SingleSelectionFormField::create('categoryID')
                        ->label('wcf.global.category')
                        ->options($this->getCategories(), labelLanguageItems: false)
                        ->required()
                        ->saveValueCallback(static function (TrophyBuilder $builder, SingleSelectionFormField $field): void {
                            $builder->setCategoryID((int)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Trophy $object, IFormField $field): void {
                            $field->value($object->categoryID);
                        }),
                    IntegerFormField::create('showOrder')
                        ->label('wcf.global.showOrder')
                        ->description('wcf.acp.trophy.showOrder.description')
                        ->minimum(0)
                        ->value(0)
                        ->saveValueCallback(static function (TrophyBuilder $builder, IntegerFormField $field): void {
                            $builder->setShowOrder($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Trophy $object, IFormField $field): void {
                            $field->value($object->showOrder);
                        }),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.trophy.isDisabled')
                        ->saveValueCallback(static function (TrophyBuilder $builder, BooleanFormField $field): void {
                            $builder->setIsDisabled((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Trophy $object, IFormField $field): void {
                            $field->value($object->isDisabled);
                        }),
                    BooleanFormField::create('awardAutomatically')
                        ->label('wcf.acp.trophy.awardAutomatically')
                        ->saveValueCallback(static function (TrophyBuilder $builder, BooleanFormField $field): void {
                            $awardAutomatically = (bool)$field->getSaveValue();
                            $builder->setAwardAutomatically($awardAutomatically);

                            // The dependent fields are not saved if the trophy is
                            // no longer awarded automatically.
                            if (!$awardAutomatically) {
                                $builder->setRevokeAutomatically(false);
                                $builder->setConditions(null);
                            }
                        })
                        ->loadValueCallback(static function (Trophy $object, IFormField $field): void {
                            $field->value($object->awardAutomatically);
                        }),
                    BooleanFormField::create('revokeAutomatically')
                        ->label('wcf.acp.trophy.revokeAutomatically')
                        ->saveValueCallback(static function (TrophyBuilder $builder, BooleanFormField $field): void {
                            $builder->setRevokeAutomatically((bool)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Trophy $object, IFormField $field): void {
                            $field->value($object->revokeAutomatically);
                        })
                        ->addDependency(
                            NonEmptyFormFieldDependency::create('awardAutomatically')
                                ->fieldId('awardAutomatically')
                        ),
                    RadioButtonFormField::create('type')
                        ->label('wcf.acp.trophy.type')
                        ->options([
                            Trophy::TYPE_BADGE => 'wcf.acp.trophy.type.badge',
                            Trophy::TYPE_IMAGE => 'wcf.acp.trophy.type.imageUpload',
                        ])
                        ->value(Trophy::TYPE_BADGE)
                        ->required()
                        ->saveValueCallback(static function (TrophyBuilder $builder, RadioButtonFormField $field): void {
                            $builder->setType((int)$field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Trophy $object, IFormField $field): void {
                            $field->value($object->type);
                        }),
                ]),
            FormContainer::create('badgeContainer')
                ->label('wcf.acp.trophy.type.badge')
                ->appendChild(
                    TrophyBadgeFormField::create('badge')
                        ->label('wcf.acp.trophy.type.badge')
                        ->required()
                        ->saveValueCallback(static function (TrophyBuilder $builder, TrophyBadgeFormField $field): void {
                            $builder->setBadge(
                                $field->getIconName(),
                                $field->getIconColor(),
                                $field->getBadgeColor(),
                                $field->getBorderColor()
                            );
                        })
                        ->loadValueCallback(static function (Trophy $object, IFormField $field): void {
                            // Image trophies keep the default badge.
                            if ($object->type === Trophy::TYPE_BADGE) {
                                $field->value([
                                    'iconName' => $object->iconName,
                                    'iconColor' => $object->iconColor,
                                    'badgeColor' => $object->badgeColor,
                                    'borderColor' => $object->borderColor,
                                ]);
                            }
                        })
                )
                ->addDependency(
                    ValueFormFieldDependency::create('type')
                        ->fieldId('type')
                        ->values([Trophy::TYPE_BADGE])
                ),
            FormContainer::create('imageContainer')
                ->label('wcf.acp.trophy.type.imageUpload')
                ->appendChild(
                    FileProcessorFormField::create('iconFileID')
                        ->objectType('com.woltlab.wcf.trophy.image')
                        ->label('wcf.acp.trophy.type.imageUpload')
                        ->description('wcf.acp.trophy.type.imageUpload.description')
                        ->singleFileUpload()
                        ->bigPreview()
                        ->required()
                        ->saveValueCallback(static function (TrophyBuilder $builder, FileProcessorFormField $field): void {
                            $builder->setIconFileID($field->getSaveValue());
                        })
                        ->loadValueCallback(static function (Trophy $object, IFormField $field): void {
                            if ($object->iconFileID !== null) {
                                $field->value($object->iconFileID);
                            }
                        })
                )
                ->addDependency(
                    ValueFormFieldDependency::create('type')
                        ->fieldId('type')
                        ->values([Trophy::TYPE_IMAGE])
                ),
            FormContainer::create('conditionsContainer')
                ->label('wcf.acp.trophy.conditions')
                ->description('wcf.acp.trophy.conditions.description')
                ->appendChild(
                    ObjectFilterFormField::create('conditions')
                        ->builder(new TrophyObjectFilterBuilder())
                        ->required()
                        ->saveValueCallback(static function (TrophyBuilder $builder, ObjectFilterFormField $field): void {
                            $builder->setConditions($field->getSaveValue());
                        })
                )
                ->addDependency(
                    NonEmptyFormFieldDependency::create('awardAutomatically')
                        ->fieldId('awardAutomatically')
                ),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function getCategories(): array
    {
        $categories = [];
        foreach (TrophyCategoryCache::getInstance()->getCategories() as $category) {
            $categories[$category->categoryID] = $category->getTitle();
        }

        return $categories;
    }
}
