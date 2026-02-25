<?php

namespace wcf\acp\form;

use wcf\data\label\group\LabelGroup;
use wcf\data\label\group\LabelGroupAction;
use wcf\data\label\group\LabelGroupEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\acl\ACLHandler;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\container\TabMenuFormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\acl\AclFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\TemplateFormNode;
use wcf\system\label\object\type\ILabelObjectTypeHandler;
use wcf\system\label\object\type\LabelObjectTypeContainer;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the label group add form.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<LabelGroup>
 */
class LabelGroupAddForm extends AbstractFormBuilderForm
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
    public $objectActionClass = LabelGroupAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = LabelGroupEditForm::class;

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
    protected function createForm()
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
                                ->i18n()
                                ->languageItemPattern('wcf.acp.label.group\d+'),
                            TextFormField::create('groupDescription')
                                ->label('wcf.global.description')
                                ->description('wcf.acp.label.group.groupDescription.description')
                                ->maximumLength(255),
                            IntegerFormField::create('showOrder')
                                ->label('wcf.global.showOrder')
                                ->minimum(0)
                                ->value(0),
                            BooleanFormField::create('forceSelection')
                                ->label('wcf.acp.label.group.forceSelection'),
                            BooleanFormField::create('sortAlphabetically')
                                ->label('wcf.acp.label.group.sortAlphabetically'),
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
    protected function finalizeForm()
    {
        parent::finalizeForm();

        // The groupName column is NOT NULL without a default. When i18n values
        // are used, hasSaveValue() returns false and groupName would be missing
        // from the data array. This processor ensures it's always present.
        $this->form->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'groupNameFallback',
                function (IFormDocument $document, array $parameters) {
                    if (!isset($parameters['data']['groupName'])) {
                        $parameters['data']['groupName'] = '';
                    }

                    return $parameters;
                }
            )
        );
    }

    #[\Override]
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['objectTypes']) && \is_array($_POST['objectTypes'])) {
            $this->objectTypes = $_POST['objectTypes'];
        }
    }

    #[\Override]
    public function validate()
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
    public function readData()
    {
        parent::readData();

        $this->setObjectTypeRelations();
    }

    #[\Override]
    public function saved()
    {
        $formData = $this->form->getData();

        if ($this->formAction === 'create') {
            $group = $this->objectAction->getReturnValues()['returnValues'];
            \assert($group instanceof LabelGroup);
            $groupID = $group->groupID;
        } else {
            $groupID = $this->formObject->groupID;
        }

        // Handle i18n groupName.
        $languageItem = 'wcf.acp.label.group' . $groupID;
        if (isset($formData['groupName_i18n'])) {
            I18nHandler::getInstance()->save(
                $formData['groupName_i18n'],
                $languageItem,
                'wcf.acp.label',
                1
            );

            if ($this->formAction === 'create') {
                \assert(isset($group));
                (new LabelGroupEditor($group))->update(['groupName' => $languageItem]);
            } else {
                (new LabelGroupEditor($this->formObject))->update(['groupName' => $languageItem]);
            }
        } elseif ($this->formAction === 'edit') {
            // Switched from i18n to plain value — remove old language items.
            I18nHandler::getInstance()->remove($languageItem);
        }

        // Save ACL.
        ACLHandler::getInstance()->save($groupID, $formData['aclPermissions_aclObjectTypeID']);

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
        if (!empty($this->objectTypes)) {
            $sql = "INSERT INTO wcf1_label_group_to_object
                                (groupID, objectTypeID, objectID)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($this->objectTypes as $objectTypeID => $data) {
                foreach ($data as $objectID) {
                    // use "0" (stored as NULL) for simple true/false states
                    if (!$objectID) {
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
        if (!empty($_POST)) {
            // use POST data
            $data = &$this->objectTypes;
        }

        foreach ($this->labelObjectTypeContainers as $objectTypeID => $container) {
            $hasData = isset($data[$objectTypeID]);
            foreach ($container as $object) {
                if (!$hasData) {
                    $object->setOptionValue(0);
                } else {
                    $optionValue = \in_array($object->getObjectID(), $data[$objectTypeID]) ? 1 : 0;
                    $object->setOptionValue($optionValue);
                }
            }
        }
    }
}
