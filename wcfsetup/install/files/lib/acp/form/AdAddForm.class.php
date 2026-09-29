<?php

namespace wcf\acp\form;

use wcf\command\ad\CreateAd;
use wcf\command\ad\UpdateAd;
use wcf\data\ad\Ad;
use wcf\data\ad\AdBuilder;
use wcf\data\DatabaseObjectBuilder;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\ad\AdHandler;
use wcf\system\ad\location\IAdLocation;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\ObjectFilterFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormChildNode;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\object\filter\builder\AdObjectFilterBuilder;

/**
 * Shows the form to create a new ad.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<Ad, AdBuilder>
 */
class AdAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.ad.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.ad.canManageAd'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_WCF_AD'];

    /**
     * @inheritDoc
     */
    public string $objectEditLinkController = AdEditForm::class;

    #[\Override]
    protected function getDatabaseObjectBuilder(): AdBuilder
    {
        if ($this->formObject !== null) {
            return AdBuilder::forUpdate($this->formObject);
        }

        return AdBuilder::forCreate();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateAd($builder);
        }

        return new CreateAd($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        $location = SelectFormField::create('objectTypeID')
            ->label('wcf.acp.ad.location')
            ->options($this->getLocationOptions(), true, false)
            ->ignoreInvalidValues()
            ->required()
            ->saveValueCallback(static function (AdBuilder $builder, IFormField $field) {
                $builder->setObjectTypeID((int)$field->getSaveValue());
            });

        $this->form->appendChildren([
            FormContainer::create('data')
                ->appendChildren([
                    TextFormField::create('adName')
                        ->label('wcf.global.name')
                        ->maximumLength(255)
                        ->required()
                        ->autoFocus()
                        ->saveValueCallback(static function (AdBuilder $builder, IFormField $field) {
                            $builder->setAdName($field->getSaveValue());
                        }),
                    MultilineTextFormField::create('ad')
                        ->label('wcf.acp.ad.ad')
                        ->description('wcf.acp.ad.ad.description')
                        ->required()
                        ->rows(10)
                        ->saveValueCallback(static function (AdBuilder $builder, IFormField $field) {
                            $builder->setAd($field->getSaveValue());
                        }),
                    ...$this->getLocationVariablesNodes($location),
                    $location,
                    IntegerFormField::create('showOrder')
                        ->label('wcf.global.showOrder')
                        ->description('wcf.acp.ad.showOrder.description')
                        ->minimum(0)
                        ->saveValueCallback(static function (AdBuilder $builder, IFormField $field) {
                            $builder->setShowOrder($field->getSaveValue());
                        }),
                ]),
            FormContainer::create('settings')
                ->label('wcf.global.settings')
                ->appendChildren([
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.ad.isDisabled')
                        ->saveValueCallback(static function (AdBuilder $builder, IFormField $field) {
                            $builder->setIsDisabled((bool)$field->getSaveValue());
                        }),
                ]),
            FormContainer::create('conditionsContainer')
                ->label('wcf.acp.ad.conditions')
                ->description('wcf.acp.ad.conditions.description')
                ->appendChild(
                    ObjectFilterFormField::create('conditions')
                        ->builder(new AdObjectFilterBuilder())
                        ->saveValueCallback(static function (AdBuilder $builder, IFormField $field) {
                            $builder->setConditions($field->getSaveValue());
                        })
                ),
        ]);
    }

    /**
     * Returns the available locations grouped by their category.
     *
     * @return list<array{label: string, value: int|string, depth: int, isSelectable?: bool}>
     */
    private function getLocationOptions(): array
    {
        $options = [];
        foreach (AdHandler::getInstance()->getLocationSelection() as $categoryLabel => $locations) {
            $options[] = [
                'label' => $categoryLabel,
                'value' => 'category' . \count($options),
                'depth' => 0,
                'isSelectable' => false,
            ];

            foreach ($locations as $objectTypeID => $locationLabel) {
                $options[] = [
                    'label' => $locationLabel,
                    'value' => $objectTypeID,
                    'depth' => 1,
                ];
            }
        }

        return $options;
    }

    /**
     * Returns the nodes that list the location specific variables that are
     * replaced within the ad, each is only shown while its location is selected.
     *
     * @return list<IFormChildNode>
     */
    private function getLocationVariablesNodes(SelectFormField $location): array
    {
        $nodes = [];
        foreach (AdHandler::getInstance()->getLocationObjectTypes() as $objectType) {
            if ($objectType->className === '' || !\is_subclass_of($objectType->className, IAdLocation::class)) {
                continue;
            }

            $adLocation = $objectType->getProcessor();
            \assert($adLocation instanceof IAdLocation);

            $nodes[] = TemplateFormNode::create('locationVariables' . $objectType->objectTypeID)
                ->templateName('__adLocationVariables')
                ->variables([
                    'variablesDescription' => $adLocation->getVariablesDescription(),
                ])
                ->addDependency(
                    ValueFormFieldDependency::create('objectTypeID')
                        ->field($location)
                        ->values([$objectType->objectTypeID])
                );
        }

        return $nodes;
    }
}
