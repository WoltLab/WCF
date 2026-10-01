<?php

namespace wcf\acp\form;

use wcf\command\label\group\CreateLabelGroup;
use wcf\command\label\group\UpdateLabelGroup;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\acl\ACLHandler;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\container\TabMenuFormContainer;
use wcf\system\form\builder\field\acl\AclFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\label\object\type\ILabelObjectTypeHandler;
use wcf\system\label\object\type\LabelObjectTypeContainer;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Shows the label group add form.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectBuilderForm<LabelGroup, LabelGroupBuilder>
 */
class LabelGroupAddForm extends AbstractDatabaseObjectBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.label.group.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.label.canManageLabel'];

    /**
     * @inheritDoc
     */
    public string $objectEditLinkController = LabelGroupEditForm::class;

    /**
     * list of label group to object type relations
     * @var array<int, int[]>
     */
    public array $objectTypes = [];

    /**
     * list of label object type handlers
     * @var ILabelObjectTypeHandler[]
     */
    protected array $labelObjectTypes = [];

    /**
     * list of label object type containers
     * @var LabelObjectTypeContainer[]
     */
    protected array $labelObjectTypeContainers = [];

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        // Initialize label object types and containers before form building
        // (which happens in checkPermissions), since the TemplateFormNode
        // in createForm() needs the containers.
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.label.objectType');
        foreach ($objectTypes as $objectType) {
            $handler = $objectType->getProcessor();
            \assert($handler instanceof ILabelObjectTypeHandler);

            $container = $handler->getContainerForObjectType($objectType);

            $this->labelObjectTypes[$objectType->objectTypeID] = $handler;
            $this->labelObjectTypeContainers[$objectType->objectTypeID] = $container;
        }
    }

    #[\Override]
    protected function getDatabaseObjectBuilder(): LabelGroupBuilder
    {
        if ($this->formObject !== null) {
            return LabelGroupBuilder::forUpdate($this->formObject);
        }

        return LabelGroupBuilder::forCreate();
    }

    #[\Override]
    protected function getCommand(DatabaseObjectBuilder $builder): callable
    {
        if ($this->formObject !== null) {
            return new UpdateLabelGroup($builder);
        }

        return new CreateLabelGroup($builder);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $tabMenu = TabMenuFormContainer::create('tabMenu');
        $tabMenu->appendChildren([
            TabFormContainer::create('general')
                ->label('wcf.global.form.data')
                ->appendChildren([
                    FormContainer::create('generalContainer')
                        ->appendChildren([
                            TextFormField::create('groupName')
                                ->label('wcf.global.title')
                                ->required()
                                ->autoFocus()
                                ->maximumLength(80)
                                ->l10n()
                                ->saveValueCallback(static function (LabelGroupBuilder $builder, TextFormField $field): void {
                                    $builder->setGroupName($field->getL10nValues());
                                })
                                ->loadValueCallback(static function (LabelGroup $object, IFormField $field): void {
                                    $field->value($object->getL10nValues('groupName'));
                                }),
                            TextFormField::create('groupDescription')
                                ->label('wcf.global.description')
                                ->description('wcf.acp.label.group.groupDescription.description')
                                ->maximumLength(255)
                                ->saveValueCallback(static function (LabelGroupBuilder $builder, TextFormField $field): void {
                                    $builder->setGroupDescription($field->getSaveValue());
                                })
                                ->loadValueCallback(static function (LabelGroup $object, IFormField $field): void {
                                    $field->value($object->groupDescription);
                                }),
                            IntegerFormField::create('showOrder')
                                ->label('wcf.global.showOrder')
                                ->minimum(0)
                                ->value(0)
                                ->saveValueCallback(static function (LabelGroupBuilder $builder, IntegerFormField $field): void {
                                    $builder->setShowOrder($field->getSaveValue());
                                })
                                ->loadValueCallback(static function (LabelGroup $object, IFormField $field): void {
                                    $field->value($object->showOrder);
                                }),
                            BooleanFormField::create('forceSelection')
                                ->label('wcf.acp.label.group.forceSelection')
                                ->saveValueCallback(static function (LabelGroupBuilder $builder, BooleanFormField $field): void {
                                    $builder->setForceSelection((bool)$field->getSaveValue());
                                })
                                ->loadValueCallback(static function (LabelGroup $object, IFormField $field): void {
                                    $field->value($object->forceSelection);
                                }),
                            BooleanFormField::create('sortAlphabetically')
                                ->label('wcf.acp.label.group.sortAlphabetically')
                                ->saveValueCallback(static function (LabelGroupBuilder $builder, BooleanFormField $field): void {
                                    $builder->setSortAlphabetically((bool)$field->getSaveValue());
                                })
                                ->loadValueCallback(static function (LabelGroup $object, IFormField $field): void {
                                    $field->value($object->sortAlphabetically);
                                }),
                            AclFormField::create('aclPermissions')
                                ->label('wcf.acl.permissions')
                                ->objectType('com.woltlab.wcf.label'),
                        ]),
                ]),
            TabFormContainer::create('connect')
                ->label('wcf.acp.label.group.category.connect')
                ->appendChildren([
                    FormContainer::create('connectElements')
                        ->appendChildren([
                            TemplateFormNode::create('labelObjectTypes')
                                ->templateName('__labelGroupObjectTypes')
                                ->variables([
                                    'labelObjectTypeContainers' => $this->labelObjectTypeContainers,
                                ])
                        ]),
                ]),
        ]);

        $this->form->appendChildren([$tabMenu]);
    }

    #[\Override]
    public function readFormParameters(): void
    {
        parent::readFormParameters();

        if (isset($_POST['objectTypes']) && \is_array($_POST['objectTypes'])) {
            // @phpstan-ignore assign.propertyType
            $this->objectTypes = ArrayUtil::toIntegerArray($_POST['objectTypes']);
        }
    }

    #[\Override]
    public function validate(): void
    {
        parent::validate();

        // Sanitize object type relations.
        foreach ($this->objectTypes as $objectTypeID => $data) {
            if (!isset($this->labelObjectTypes[$objectTypeID])) {
                unset($this->objectTypes[$objectTypeID]);
            }
        }
    }

    #[\Override]
    public function readData(): void
    {
        parent::readData();

        $this->setObjectTypeRelations();
    }

    #[\Override]
    public function saved(): void
    {
        $groupID = $this->object->groupID;

        // Save ACL.
        ACLHandler::getInstance()->save($groupID, $this->form->getData()['aclPermissions_aclObjectTypeID']);

        // Save object type relations.
        $this->saveObjectTypeRelations($groupID);

        foreach ($this->labelObjectTypes as $labelObjectType) {
            $labelObjectType->save();
        }

        // Reset object type selections for create form.
        if ($this->formAction === 'create') {
            $this->objectTypes = [];
            $this->setObjectTypeRelations();
        }

        parent::saved();
    }

    /**
     * Saves label group to object relations.
     */
    protected function saveObjectTypeRelations(int $groupID): void
    {
        WCF::getDB()->beginTransaction();

        // remove old relations
        $sql = "DELETE FROM wcf1_label_group_to_object
                WHERE       groupID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$groupID]);

        // insert new relations
        if ($this->objectTypes !== []) {
            $sql = "INSERT INTO wcf1_label_group_to_object
                                (groupID, objectTypeID, objectID)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($this->objectTypes as $objectTypeID => $data) {
                foreach ($data as $objectID) {
                    // use "0" (stored as NULL) for simple true/false states
                    if ($objectID === 0) {
                        $objectID = null;
                    }

                    $statement->execute([
                        $groupID,
                        $objectTypeID,
                        $objectID,
                    ]);
                }
            }
        }

        WCF::getDB()->commitTransaction();
    }

    /**
     * Sets object type relations.
     *
     * @param ?array<int, int[]> $data
     */
    protected function setObjectTypeRelations(?array $data = null): void
    {
        if ($_POST !== []) {
            // use POST data
            $data = &$this->objectTypes;
        }

        foreach ($this->labelObjectTypeContainers as $objectTypeID => $container) {
            $hasData = isset($data[$objectTypeID]);
            foreach ($container as $object) {
                if (!$hasData) {
                    $object->setOptionValue(0);
                } else {
                    $optionValue = \in_array($object->getObjectID(), $data[$objectTypeID], true) ? 1 : 0;
                    $object->setOptionValue($optionValue);
                }
            }
        }
    }
}
