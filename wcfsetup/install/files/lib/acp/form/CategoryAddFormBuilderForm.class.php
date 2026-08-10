<?php

namespace wcf\acp\form;

use wcf\data\category\Category;
use wcf\data\category\CategoryAction;
use wcf\data\category\CategoryEditor;
use wcf\data\category\UncachedCategoryNodeTree;
use wcf\data\DatabaseObject;
use wcf\data\IStorableObject;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\acl\ACLHandler;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\category\ICategoryType;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\acl\AclFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormChildNode;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Abstract implementation of a form for creating categories based on the form builder.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @extends AbstractFormBuilderForm<Category>
 */
abstract class CategoryAddFormBuilderForm extends AbstractFormBuilderForm
{
    /**
     * id of the category acl object type
     */
    public int $aclObjectTypeID = 0;

    /**
     * name of the controller used to add new categories
     */
    public string $addController = '';

    /**
     * name of the controller used to edit categories
     */
    public string $editController = '';

    /**
     * name of the controller used to list the categories
     */
    public string $listController = '';

    /**
     * category object type object
     */
    public ObjectType $objectType;

    /**
     * name of the category object type
     */
    public string $objectTypeName = '';

    /**
     * id of the package the created package belongs to
     */
    public int $packageID = 0;

    /**
     * id of the pre-selected parent category, read from the request
     * @since 6.3
     */
    public ?int $parentCategoryID = null;

    /**
     * language item with the page title
     * @deprecated 6.3 No longer in use.
     */
    public string $pageTitle = 'wcf.category.add';

    /**
     * @inheritDoc
     */
    public $templateName = 'categoryAddFormBuilder';

    /**
     * @inheritDoc
     */
    public $objectActionClass = CategoryAction::class;

    /**
     * @inheritDoc
     */
    public $additionalFields = [
        'title' => '',
    ];

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function __run()
    {
        $this->autosetControllers();

        return parent::__run();
    }

    private function autosetControllers(): void
    {
        $classNameParts = \explode('\\', static::class);
        $className = \array_pop($classNameParts);

        if ($this->addController === '') {
            $this->addController = \str_replace(['AddForm', 'EditForm'], 'Add', $className);
        }
        if ($this->editController === '') {
            $this->editController = \str_replace(['AddForm', 'EditForm'], 'Edit', $className);
        }
        if ($this->listController === '') {
            $this->listController = \str_replace(['AddForm', 'EditForm'], 'List', $className);
        }
    }

    private function loadObjectType(): void
    {
        $objectType = CategoryHandler::getInstance()->getObjectTypeByName($this->objectTypeName);
        if ($objectType === null) {
            throw new InvalidObjectTypeException($this->objectTypeName, 'com.woltlab.wcf.category');
        }
        $this->objectType = $objectType;

        $aclObjectTypeName = $this->objectType->getProcessor()->getObjectTypeName('com.woltlab.wcf.acl');
        if ($aclObjectTypeName) {
            $this->aclObjectTypeID = ACLHandler::getInstance()->getObjectTypeID($aclObjectTypeName);
        }

        if ($this->packageID === 0) {
            $this->packageID = $this->objectType->packageID;
        }
    }

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->loadObjectType();

        if ($this->formAction !== 'create') {
            $this->readFormObject();
        }

        if (isset($_GET['parentCategoryID'])) {
            $parentCategoryID = \intval($_GET['parentCategoryID']);
            $category = CategoryHandler::getInstance()->getCategory($parentCategoryID);
            if ($category !== null && $category->objectTypeID === $this->objectType->getObjectID()) {
                $this->parentCategoryID = $parentCategoryID;
            }
        }
    }

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $processor = $this->getObjectTypeProcessor();

        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren($this->getGeneralFormFields()),
            FormContainer::create('position')
                ->label($processor->getLanguageVariable('position'))
                ->appendChildren($this->getPositionFormFields()),
            FormContainer::create('permissions')
                ->label('wcf.acl.permissions')
                ->appendChildren($this->getPermissionFormFields()),
        ]);
    }

    /**
     * @return IFormChildNode[]
     */
    protected function getGeneralFormFields(): array
    {
        $processor = $this->getObjectTypeProcessor();

        $formFields = [
            TitleFormField::create()
                ->label($processor->getLanguageVariable('title'))
                ->description($processor->getLanguageVariable('title.description', true))
                ->maximumLength(255)
                ->i18n()
                ->languageItemPattern($processor->getI18nLangVarPrefix() . '.title.category\d+')
                ->required(),
        ];

        if ($processor->hasDescription()) {
            $formFields[] = MultilineTextFormField::create('description')
                ->label($processor->getLanguageVariable('description'))
                ->description($processor->getLanguageVariable('description.description', true))
                ->maximumLength(5000)
                ->rows(10)
                ->i18n()
                ->languageItemPattern(
                    $processor->getI18nLangVarPrefix() . '.description.category\d+'
                )
                ->required($processor->forceDescription());
        }

        $formFields[] = BooleanFormField::create('descriptionUseHtml')
            ->label($processor->getLanguageVariable('descriptionUseHtml'))
            ->available(
                $processor->hasDescription() && $processor->supportsHtmlDescription()
            );
        $formFields[] = BooleanFormField::create('isDisabled')
            ->label($processor->getLanguageVariable('isDisabled'))
            ->description($processor->getLanguageVariable('isDisabled.description', true));

        return $formFields;
    }

    /**
     * @return IFormChildNode[]
     */
    protected function getPositionFormFields(): array
    {
        $processor = $this->getObjectTypeProcessor();

        $categoryNodeTree = new UncachedCategoryNodeTree(
            $this->objectType->objectType,
            0,
            true,
            [$this->formObject instanceof DatabaseObject ? $this->formObject->getObjectID() : -1]
        );

        $maximumNestingLevel = $processor->getMaximumNestingLevel();
        if ($maximumNestingLevel !== -1) {
            $categoryNodeTree->setMaxDepth($maximumNestingLevel - 1);
        }

        $positionFormField = null;
        if ($maximumNestingLevel === 0) {
            $positionFormField = ShowOrderFormField::create()
                ->description($processor->getLanguageVariable('showOrder.description', true))
                ->options($categoryNodeTree, true)
                ->nullable()
                ->required();
        } else {
            // Provide a simple integer input instead because we cannot
            // dynamically filter the list of categories in the position field.
            $positionFormField = IntegerFormField::create('showOrder')
                ->label('wcf.form.field.showOrder')
                ->description($processor->getLanguageVariable('showOrder.description', true))
                ->minimum(0)
                ->value(0)
                ->required();
        }

        return [
            SelectFormField::create('parentCategoryID')
                ->label($processor->getLanguageVariable('parentCategoryID'))
                ->description(
                    $processor->getLanguageVariable('parentCategoryID.description', true)
                )
                ->options($categoryNodeTree, true)
                ->available((bool)$this->getObjectTypeProcessor()->getMaximumNestingLevel())
                ->value($this->parentCategoryID)
                ->addValidator(
                    new FormFieldValidator(
                        'recursion',
                        function (SelectFormField $formField) use ($processor) {
                            if ((int)$formField->getValue() === 0) {
                                return;
                            }

                            if (!($this->formObject instanceof DatabaseObject)) {
                                return;
                            }

                            if ($this->formObject->getObjectID() === $formField->getValue()) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'invalid',
                                        $processor->getLanguageVariable(
                                            'parentCategoryID.error.invalid'
                                        )
                                    )
                                );

                                return;
                            }

                            $childCategories = CategoryHandler::getInstance()->getChildCategories(
                                $this->formObject->getObjectID(),
                                $this->objectType->getObjectID()
                            );

                            if (isset($childCategories[$formField->getValue()])) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'invalid',
                                        $processor->getLanguageVariable(
                                            'parentCategoryID.error.invalid'
                                        )
                                    )
                                );
                            }
                        }
                    )
                )
                ->addValidator(
                    new FormFieldValidator(
                        'nestingLevel',
                        static function (SelectFormField $formField) use ($processor) {
                            if ((int)$formField->getValue() === 0) {
                                return;
                            }

                            if ($processor->getMaximumNestingLevel() === -1) {
                                return;
                            }

                            if ($processor->getMaximumNestingLevel() === 0) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'invalid',
                                        $processor->getLanguageVariable(
                                            'parentCategoryID.error.invalid'
                                        )
                                    )
                                );

                                return;
                            }

                            $category = CategoryHandler::getInstance()->getCategory(
                                $formField->getValue()
                            );
                            $nestingLevel = \count($category->getParentCategories()) + 1;
                            if ($nestingLevel > $processor->getMaximumNestingLevel()) {
                                $formField->addValidationError(
                                    new FormFieldValidationError(
                                        'invalid',
                                        $processor->getLanguageVariable(
                                            'parentCategoryID.error.invalid'
                                        )
                                    )
                                );
                            }
                        }
                    )
                ),
            $positionFormField,
        ];
    }

    /**
     * @return IFormChildNode[]
     */
    protected function getPermissionFormFields(): array
    {
        $aclObjectTypeName = $this->getObjectTypeProcessor()->getObjectTypeName('com.woltlab.wcf.acl');
        if ($aclObjectTypeName === null) {
            return [];
        }

        $aclObjectType = ObjectTypeCache::getInstance()->getObjectType(
            ACLHandler::getInstance()->getObjectTypeID($aclObjectTypeName)
        );

        return [
            AclFormField::create('aclPermissions')
                ->label('wcf.acl.permissions')
                ->objectType($aclObjectType->objectType)
        ];
    }

    protected function getObjectTypeProcessor(): ICategoryType
    {
        return $this->objectType->getProcessor();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        $categoryNodeTree = new UncachedCategoryNodeTree($this->objectType->objectType, 0, true);

        WCF::getTPL()->assign([
            'addController' => $this->addController,
            'editController' => $this->editController,
            'listController' => $this->listController,
            'objectType' => $this->objectType,
            'categoryNodeList' => $categoryNodeTree->getIterator(),
        ]);
    }

    #[\Override]
    protected function finalizeForm()
    {
        parent::finalizeForm();

        $this->form->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'data',
                function (IFormDocument $document, array $parameters) {
                    $parameters['data']['objectTypeID'] = $this->objectType->getObjectID();

                    if ((int)($parameters['data']['parentCategoryID'] ?? 0) === 0) {
                        $parameters['data']['parentCategoryID'] = 0;
                    }

                    if (isset($parameters['additionalData'])) {
                        $parameters['data']['additionalData'] = \serialize($parameters['additionalData']);
                    }

                    return $parameters;
                },
                function (IFormDocument $document, array $data, IStorableObject $object) {
                    if (!$data['parentCategoryID']) {
                        $data['parentCategoryID'] = null;
                    }

                    return $data;
                }
            )
        );
    }

    protected function readFormObject(): void
    {
        $this->formObject = new Category(\intval($_REQUEST['id'] ?? 0));

        if ($this->formObject === null || $this->formObject->categoryID === 0) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    public function saved()
    {
        $processor = $this->getObjectTypeProcessor();
        $updateData = [];

        if ($this->formAction === 'create') {
            $category = $this->objectAction->getReturnValues()['returnValues'];
            \assert($category instanceof Category);
        } else {
            $category = new Category($this->formObject->categoryID);
        }

        $titleField = $this->form->getFormField('title');
        \assert($titleField instanceof TitleFormField);
        $titleLanguageItem = "{$processor->getI18nLangVarPrefix()}.title.category{$category->getObjectID()}";

        if ($titleField->hasPlainValue()) {
            I18nHandler::getInstance()->remove($titleLanguageItem);
        } else {
            $updateData['title'] = $titleLanguageItem;
            I18nHandler::getInstance()->save(
                $titleField->getPrefixedId(),
                $titleLanguageItem,
                $processor->getTitleLangVarCategory(),
                $category->getObjectType()->packageID
            );
        }

        if ($processor->hasDescription()) {
            $descriptionField = $this->form->getFormField('description');
            \assert($descriptionField instanceof MultilineTextFormField);
            $descriptionLanguageItem = "{$processor->getI18nLangVarPrefix()}.description.category{$category->getObjectID()}";

            if ($descriptionField->hasPlainValue()) {
                I18nHandler::getInstance()->remove($descriptionLanguageItem);
            } else {
                $updateData['description'] = $descriptionLanguageItem;
                I18nHandler::getInstance()->save(
                    $descriptionField->getPrefixedId(),
                    $descriptionLanguageItem,
                    $processor->getDescriptionLangVarCategory(),
                    $category->getObjectType()->packageID
                );
            }
        }

        $formData = $this->form->getData();
        if (isset($formData['aclPermissions_aclObjectTypeID'])) {
            ACLHandler::getInstance()->save(
                $category->getObjectID(),
                $formData['aclPermissions_aclObjectTypeID']
            );
            CategoryPermissionHandler::getInstance()->resetCache();
        }

        if ($updateData !== []) {
            (new CategoryEditor($category))->update($updateData);
        }

        CategoryEditor::resetCache();

        parent::saved();
    }

    #[\Override]
    public function checkPermissions()
    {
        AbstractForm::checkPermissions();

        $this->checkCategoryPermissions();

        $this->buildForm();
    }

    protected function checkCategoryPermissions(): void
    {
        $processor = $this->getObjectTypeProcessor();

        if ($this->formObject instanceof DatabaseObject) {
            if ($this->formObject->objectTypeID !== $this->objectType->getObjectID()) {
                throw new IllegalLinkException();
            }

            if (!$processor->canEditCategory()) {
                throw new PermissionDeniedException();
            }

            return;
        }

        if (!$processor->canAddCategory()) {
            throw new PermissionDeniedException();
        }
    }
}
