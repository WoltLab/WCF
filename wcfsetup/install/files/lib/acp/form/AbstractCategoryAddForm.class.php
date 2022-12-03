<?php

namespace wcf\acp\form;

use wcf\data\category\Category;
use wcf\data\category\CategoryAction;
use wcf\data\category\CategoryNodeTree;
use wcf\data\category\UncachedCategoryNodeTree;
use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\acl\ACLHandler;
use wcf\system\category\CategoryHandler;
use wcf\system\category\ICategoryType;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\container\TabMenuFormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\acl\AclFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;

/**
 * Abstract implementation of a form to create categories.
 *
 * @author  Florian Gail
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Form
 *
 * @property CategoryAction $objectAction
 * @property Category|null  $formObject
 */
abstract class AbstractCategoryAddForm extends AbstractFormBuilderForm
{
    /**
     * name of the controller used to add new categories
     */
    public string $addController;

    /**
     * tree with the category nodes
     * @var UncachedCategoryNodeTree
     */
    public CategoryNodeTree $categoryNodeTree;

    /**
     * name of the controller used to edit categories
     */
    public string $editController;

    /**
     * name of the controller used to list the categories
     */
    public string $listController;

    /**
     * category object type object
     */
    public ObjectType $objectType;

    /**
     * category acl object type object
     */
    public ?ObjectType $aclObjectType;

    /**
     * id of the package the created package belongs to
     */
    public int $packageID;

    /**
     * language item with the page title
     */
    public string $pageTitle;

    /**
     * @inheritDoc
     */
    public $templateName = 'categoryAdd';

    /**
     * @inheritDoc
     */
    public $objectActionClass = CategoryAction::class;

    /**
     * @inheritDoc
     */
    public function __run()
    {
        $classNameParts = \explode('\\', static::class);
        $className = \array_pop($classNameParts);

        // autoset controllers
        if (!isset($this->listController)) {
            $this->listController = \str_replace(['AddForm', 'EditForm'], 'List', $className);
        }
        if (!isset($this->addController)) {
            $this->addController = \str_replace(['AddForm', 'EditForm'], 'Add', $className);
        }
        if (!isset($this->editController)) {
            if (!empty($this->objectEditLinkController)) {
                $classNameParts = \explode('\\', $this->objectEditLinkController);
                $className = \array_pop($classNameParts);

                $this->editController = \preg_replace('/Form$/', '', $className);
            } else {
                $this->editController = \str_replace(['AddForm', 'EditForm'], 'Edit', $className);
            }
        }

        $objectTypeName = $this->getObjectTypeName();
        $objectType = CategoryHandler::getInstance()->getObjectTypeByName($objectTypeName);
        if ($objectType === null) {
            throw new InvalidObjectTypeException($this->objectTypeName, 'com.woltlab.wcf.category');
        }
        $this->objectType = $objectType;

        // get acl object type id
        $aclObjectTypeName = $this->getObjectTypeProcessor()->getObjectTypeName('com.woltlab.wcf.acl');
        if ($aclObjectTypeName) {
            $this->aclObjectType = ObjectTypeCache::getInstance()->getObjectType(
                ACLHandler::getInstance()->getObjectTypeID($aclObjectTypeName)
            );
        }

        // autoset package id
        if (!$this->packageID) {
            $this->packageID = $this->objectType->packageID;
        }

        return parent::__run();
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if ($this->formAction !== 'create') {
            $this->readFormObject();
        }
    }

    /**
     * @inheritDoc
     */
    public function checkPermissions()
    {
        AbstractForm::checkPermissions();

        // check permissions
        $this->checkCategoryPermissions();

        $this->buildForm();
    }

    /**
     * Checks if the active user has the needed permissions to add a new category.
     */
    protected function checkCategoryPermissions(): void
    {
        $processor = $this->getObjectTypeProcessor();
        \assert($processor instanceof ICategoryType);

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

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        $processor = $this->getObjectTypeProcessor();

        $categoryNodeTree = new UncachedCategoryNodeTree(
            $this->objectType->objectType,
            0,
            true,
            [$this->formObject instanceof DatabaseObject ? $this->formObject->getObjectID() : -1]
        );

        $maximumNestingLevel = $processor->getMaximumNestingLevel();
        if (\is_numeric($maximumNestingLevel) && $maximumNestingLevel !== -1) {
            $categoryNodeTree->setMaxDepth($maximumNestingLevel - 1);
        }

        $this->form->appendChildren([
            TabMenuFormContainer::create('tabMenu')
                ->appendChildren([
                    TabFormContainer::create('general')
                        ->label('wcf.global.form.data')
                        ->appendChildren([
                            FormContainer::create('data')
                                ->label('wcf.global.form.data')
                                ->appendChildren([
                                    TitleFormField::create()
                                        ->label($processor->getLanguageVariable('title'))
                                        ->description($processor->getLanguageVariable('title.description', true))
                                        ->maximumLength(255)
                                        ->i18n()
                                        ->languageItemPattern($processor->getI18nLangVarPrefix() . '.title.category\d+')
                                        ->required(),
                                    MultilineTextFormField::create('description')
                                        ->label($processor->getLanguageVariable('description'))
                                        ->description($processor->getLanguageVariable('description.description', true))
                                        ->maximumLength(5000)
                                        ->rows(10)
                                        ->i18n()
                                        ->languageItemPattern(
                                            $processor->getI18nLangVarPrefix() . '.description.category\d+'
                                        )
                                        ->available($processor->hasDescription())
                                        ->required($processor->forceDescription()),
                                    BooleanFormField::create('descriptionUseHtml')
                                        ->label($processor->getLanguageVariable('descriptionUseHtml'))
                                        ->available(
                                            $processor->hasDescription() && $processor->supportsHtmlDescription()
                                        ),
                                    BooleanFormField::create('isDisabled')
                                        ->label($processor->getLanguageVariable('isDisabled'))
                                        ->description($processor->getLanguageVariable('isDisabled.description', true)),
                                ]),
                        ]),
                    TabFormContainer::create('appearanceTab')
                        ->label('wcf.category.appearance')
                        ->appendChildren([
                            FormContainer::create('position')
                                ->label($processor->getLanguageVariable('position'))
                                ->appendChildren([
                                    SingleSelectionFormField::create('parentCategoryID')
                                        ->label($processor->getLanguageVariable('parentCategoryID'))
                                        ->description(
                                            $processor->getLanguageVariable('parentCategoryID.description', true)
                                        )
                                        ->options($categoryNodeTree, true)
                                        ->allowEmptySelection()
                                        //->nullable() // it doesn't make any sense, but this column is not nullable
                                        ->addValidator(
                                            new FormFieldValidator(
                                                'recursion',
                                                function (SingleSelectionFormField $formField) use ($processor) {
                                                    if (empty($formField->getValue())) {
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
                                                static function (SingleSelectionFormField $formField) use ($processor) {
                                                    if (empty($formField->getValue())) {
                                                        return;
                                                    }

                                                    if ($processor->getMaximumNestingLevel() === -1) {
                                                        return;
                                                    }

                                                    if (!$processor->getMaximumNestingLevel()) {
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
                                        )
                                        ->available($this->getObjectTypeProcessor()->getMaximumNestingLevel()),
                                    ShowOrderFormField::create()
                                        ->description($processor->getLanguageVariable('showOrder.description', true))
                                        ->options($categoryNodeTree, true)
                                        ->nullable()
                                        ->required(),
                                ]),
                        ]),
                    TabFormContainer::create('permissionsTab')
                        ->label('wcf.category.permissions')
                        ->appendChildren([
                            FormContainer::create('permissions')
                                ->label('wcf.category.permissions'),
                        ]),
                ]),
        ]);

        if (!empty($this->aclObjectType)) {
            /** @var FormContainer $permissionsContainer */
            $permissionsContainer = $this->form->getNodeById('permissions');
            $permissionsContainer->appendChild(
                AclFormField::create('aclPermissions')
                    ->label('wcf.acl.permissions')
                    ->objectType($this->aclObjectType->objectType)
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function buildForm()
    {
        parent::buildForm();

        $this->form->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'additionalData',
                function (IFormDocument $document, array $parameters) {
                    if (!isset($parameters['additionalData'])) {
                        $parameters['additionalData'] = [];
                    }

                    return $parameters;
                }
            )
        );
    }

    /**
     * @inheritDoc
     */
    protected function finalizeForm()
    {
        parent::finalizeForm();

        // make sure these processors are queued at the very end
        $this->form->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'processor',
                function (IFormDocument $document, array $parameters) {
                    $parameters['objectTypeProcessor'] = $this->getObjectTypeProcessor();

                    return $parameters;
                }
            )
        );

        $this->form->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'data',
                function (IFormDocument $document, array $parameters) {
                    $parameters['data']['objectTypeID'] = $this->objectType->getObjectID();

                    return $parameters;
                }
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->readCategories();
    }

    /**
     * Reads the categories.
     */
    protected function readCategories(): void
    {
        $this->categoryNodeTree = new UncachedCategoryNodeTree($this->objectType->objectType, 0, true);
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        if (!isset($this->pageTitle)) {
            switch ($this->formAction) {
                case 'create':
                    $this->pageTitle = 'wcf.category.add';
                    break;

                case 'edit':
                    $this->pageTitle = 'wcf.category.edit';
                    break;
            }
        }

        WCF::getTPL()->assign([
            'addController' => $this->addController,
            'editController' => $this->editController,
            'listController' => $this->listController,
            'objectType' => $this->objectType,
            'categoryNodeList' => $this->categoryNodeTree->getIterator(),
            'pageTitle' => $this->pageTitle,
        ]);
    }

    /**
     * Returns the category object type's name.
     */
    abstract public function getObjectTypeName(): string;

    /**
     * Returns the category processor.
     */
    public function getObjectTypeProcessor(): ICategoryType
    {
        return $this->objectType->getProcessor();
    }

    /**
     * @throws IllegalLinkException
     */
    protected function readFormObject(): void
    {
        if (!empty($_REQUEST['id']) && \is_numeric($_REQUEST['id'])) {
            $this->formObject = new Category((int)$_REQUEST['id']);
        }

        if ($this->formObject === null || !$this->formObject->getObjectID()) {
            throw new IllegalLinkException();
        }
    }
}
