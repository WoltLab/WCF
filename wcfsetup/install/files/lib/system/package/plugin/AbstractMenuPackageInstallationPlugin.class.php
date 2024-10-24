<?php

namespace wcf\system\package\plugin;

use wcf\data\acp\menu\item\ACPMenuItem;
use wcf\data\DatabaseObjectList;
use wcf\page\IPage;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IIdempotentPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\ClassNameFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\option\OptionFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\user\group\option\UserGroupOptionFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\Url;

/**
 * Abstract implementation of a package installation plugin for menu items.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractMenuPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IIdempotentPackageInstallationPlugin
{
    // we do no implement `IGuiPackageInstallationPlugin` but instead just
    // provide the default implementation to ensure backwards compatibility
    // with third-party packages containing classes that extend this abstract
    // class
    use TXmlGuiPackageInstallationPlugin;

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM " . $this->application . "1_" . $this->tableName . "
                WHERE       menuItem = ?
                        AND packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($items as $item) {
            $statement->execute([
                $item['attributes']['name'],
                $this->installation->getPackageID(),
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
        // adjust show order
        $showOrder = $data['elements']['showorder'] ?? null;
        $parent = $data['elements']['parent'] ?? '';
        $showOrder = $this->getShowOrder($showOrder, $parent, 'parentMenuItem');

        // merge values and default values
        return [
            'menuItem' => $data['attributes']['name'],
            'menuItemController' => $data['elements']['controller'] ?? '',
            'menuItemLink' => $data['elements']['link'] ?? '',
            'options' => isset($data['elements']['options']) ? StringUtil::normalizeCsv($data['elements']['options']) : '',
            'parentMenuItem' => $data['elements']['parent'] ?? '',
            'permissions' => isset($data['elements']['permissions']) ? StringUtil::normalizeCsv($data['elements']['permissions']) : '',
            'showOrder' => $showOrder,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function validateImport(array $data)
    {
        if (empty($data['parentMenuItem'])) {
            return;
        }

        $sql = "SELECT  COUNT(menuItemID)
                FROM    " . $this->application . "1_" . $this->tableName . "
                WHERE   menuItem = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$data['parentMenuItem']]);

        if (!$statement->fetchSingleColumn()) {
            throw new SystemException("Unable to find parent 'menu item' with name '" . $data['parentMenuItem'] . "' for 'menu item' with name '" . $data['menuItem'] . "'.");
        }
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    " . $this->application . "1_" . $this->tableName . "
                WHERE   menuItem = ?
                    AND packageID = ?";
        $parameters = [
            $data['menuItem'],
            $this->installation->getPackageID(),
        ];

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
    }

    /**
     * @inheritDoc
     * @since   3.1
     */
    public static function getSyncDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function addFormFields(IFormDocument $form)
    {
        /** @var IFormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

        $dataContainer->appendChildren([
            TextFormField::create('menuItem')
                ->objectProperty('name')
                ->label('wcf.acp.pip.abstractMenu.menuItem')
                ->addValidator(new FormFieldValidator('uniqueness', function (TextFormField $formField) {
                    if (
                        $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                        || $this->editedEntry->getAttribute('name') !== $formField->getValue()
                    ) {
                        // replace `Editor` with `List`
                        $listClassName = \substr($this->className, 0, -6) . 'List';

                        /** @var DatabaseObjectList $menuItemList */
                        $menuItemList = new $listClassName();
                        $menuItemList->getConditionBuilder()->add('menuItem = ?', [$formField->getValue()]);

                        if ($menuItemList->countObjects() > 0) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.acp.pip.abstractMenu.menuItem.error.notUnique'
                                )
                            );
                        }
                    }
                })),

            SingleSelectionFormField::create('parentMenuItem')
                ->objectProperty('parent')
                ->label('wcf.acp.pip.abstractMenu.parentMenuItem')
                ->filterable()
                ->options(function () {
                    $menuStructure = $this->getMenuStructureData()['structure'];

                    $options = [
                        [
                            'depth' => 0,
                            'label' => 'wcf.global.noSelection',
                            'value' => '',
                        ],
                    ];

                    $buildOptions = static function ($parent = '', $depth = 0) use ($menuStructure, &$buildOptions) {
                        // only consider menu items until the third level (thus only parent
                        // menu items until the second level) as potential parent menu items
                        if ($depth > 2) {
                            return [];
                        }

                        $options = [];
                        foreach ($menuStructure[$parent] as $menuItem) {
                            $options[] = [
                                'depth' => $depth,
                                'label' => $menuItem->menuItem,
                                'value' => $menuItem->menuItem,
                            ];

                            if (isset($menuStructure[$menuItem->menuItem])) {
                                $options = \array_merge($options, $buildOptions($menuItem->menuItem, $depth + 1));
                            }
                        }

                        return $options;
                    };

                    return \array_merge($options, $buildOptions());
                }, true)
                ->value('')
                ->addValidator(new FormFieldValidator(
                    'selfChildAsParent',
                    function (SingleSelectionFormField $formField) {
                        if (
                            $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_UPDATE
                            && $formField->getSaveValue() !== ''
                        ) {
                            /** @var TextFormField $menuItemField */
                            $menuItemField = $formField->getDocument()->getNodeById('menuItem');
                            $menuItem = $menuItemField->getSaveValue();
                            $parentMenuItem = $formField->getSaveValue();

                            if ($menuItem === $parentMenuItem) {
                                $formField->addValidationError(new FormFieldValidationError(
                                    'selfParent',
                                    'wcf.acp.pip.abstractMenu.parentMenuItem.error.selfParent'
                                ));
                            } else {
                                $menuStructure = $this->getMenuStructureData()['structure'];

                                $checkChildren = static function ($menuItem) use (
                                    $formField,
                                    $menuStructure,
                                    $parentMenuItem,
                                    &$checkChildren
                                ) {
                                    if (isset($menuStructure[$menuItem])) {
                                        /** @var ACPMenuItem $childMenuItem */
                                        foreach ($menuStructure[$menuItem] as $childMenuItem) {
                                            if ($childMenuItem->menuItem === $parentMenuItem) {
                                                $formField->addValidationError(new FormFieldValidationError(
                                                    'childAsParent',
                                                    'wcf.acp.pip.abstractMenu.parentMenuItem.error.childAsParent'
                                                ));

                                                return false;
                                            } else {
                                                if (!$checkChildren($childMenuItem->menuItem)) {
                                                    return false;
                                                }
                                            }
                                        }
                                    }

                                    return true;
                                };

                                $checkChildren($menuItem);
                            }
                        }
                    }
                )),

            ClassNameFormField::create('menuItemController')
                ->objectProperty('controller')
                ->label('wcf.acp.pip.abstractMenu.menuItemController')
                ->implementedInterface(IPage::class),

            TextFormField::create('menuItemLink')
                ->objectProperty('link')
                ->label('wcf.acp.pip.abstractMenu.menuItemLink')
                ->description('wcf.acp.pip.abstractMenu.menuItemLink.description')
                ->objectProperty('link')
                ->addValidator(new FormFieldValidator('linkSpecified', function (TextFormField $formField) {
                    /** @var TextFormField $menuItem */
                    $menuItem = $formField->getDocument()->getNodeById('menuItem');

                    /** @var ClassNameFormField $menuItemController */
                    $menuItemController = $formField->getDocument()->getNodeById('menuItemController');

                    // ensure that either a menu item controller is specified or a link
                    // and workaround for special ACP menu item `wcf.acp.menu.link.option.category`
                    if (
                        $formField->getSaveValue() === '' && $menuItemController->getSaveValue() === ''
                        && (!($this instanceof ACPMenuPackageInstallationPlugin) || $menuItem->getSaveValue() !== 'wcf.acp.menu.link.option.category')
                    ) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'noLinkSpecified',
                                'wcf.acp.pip.abstractMenu.menuItemLink.error.noLinkSpecified'
                            )
                        );
                    }
                }))
                ->addValidator(new FormFieldValidator('format', static function (TextFormField $formField) {
                    if ($formField->getSaveValue() !== '') {
                        /** @var ClassNameFormField $menuItemController */
                        $menuItemController = $formField->getDocument()->getNodeById('menuItemController');

                        if (!$menuItemController->getSaveValue() && !Url::is($formField->getSaveValue())) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'noLink',
                                    'wcf.acp.pip.abstractMenu.menuItemLink.error.noLink'
                                )
                            );
                        }
                    }
                })),

            OptionFormField::create()
                ->description('wcf.acp.pip.abstractMenu.options.description')
                ->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
                ->packageIDs(\array_merge(
                    [$this->installation->getPackage()->packageID],
                    \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                )),

            UserGroupOptionFormField::create()
                ->description('wcf.acp.pip.abstractMenu.permissions.description')
                ->saveValueType(OptionFormField::SAVE_VALUE_TYPE_CSV)
                ->packageIDs(\array_merge(
                    [$this->installation->getPackage()->packageID],
                    \array_keys($this->installation->getPackage()->getAllRequiredPackages())
                )),

            IntegerFormField::create('showOrder')
                ->objectProperty('showorder')
                ->label('wcf.form.field.showOrder')
                ->description('wcf.acp.pip.abstractMenu.showOrder.description')
                ->objectProperty('showorder')
                ->minimum(1)
                ->nullable(),
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'menuItem' => $element->getAttribute('name'),
            'packageID' => $this->installation->getPackage()->packageID,
        ];

        $parentMenuItem = $element->getElementsByTagName('parent')->item(0);
        if ($parentMenuItem !== null) {
            $data['parentMenuItem'] = $parentMenuItem->nodeValue;
        } elseif ($saveData) {
            $data['parentMenuItem'] = '';
        }

        $controller = $element->getElementsByTagName('controller')->item(0);
        if ($controller !== null) {
            $data['menuItemController'] = $controller->nodeValue;
        } elseif ($saveData) {
            $data['menuItemController'] = '';
        }

        $link = $element->getElementsByTagName('link')->item(0);
        if ($link !== null) {
            $data['menuItemLink'] = $link->nodeValue;
        } elseif ($saveData) {
            $data['menuItemLink'] = '';
        }

        $options = $element->getElementsByTagName('options')->item(0);
        if ($options !== null) {
            $data['options'] = $options->nodeValue;
        } elseif ($saveData) {
            $data['options'] = '';
        }

        $permissions = $element->getElementsByTagName('permissions')->item(0);
        if ($permissions !== null) {
            $data['permissions'] = $permissions->nodeValue;
        } elseif ($saveData) {
            $data['permissions'] = '';
        }

        $showOrder = $element->getElementsByTagName('showorder')->item(0);
        if ($showOrder !== null) {
            $data['showOrder'] = $showOrder->nodeValue;
        }
        if ($saveData && $this->editedEntry === null) {
            // only set explicit showOrder when adding new menu item
            $data['showOrder'] = $this->getShowOrder(
                $data['showOrder'] ?? null,
                $data['parentMenuItem'],
                'parentMenuItem'
            );
        }

        return $data;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getElementIdentifier(\DOMElement $element)
    {
        return $element->getAttribute('name');
    }

    /**
     * Returns data on the structure of the menu.
     *
     * @return  array
     */
    protected function getMenuStructureData()
    {
        // replace `Editor` with `List`
        $listClassName = \substr($this->className, 0, -6) . 'List';

        /** @var DatabaseObjectList $menuItemList */
        $menuItemList = new $listClassName();
        $menuItemList->getConditionBuilder()->add('packageID IN (?)', [
            \array_merge(
                [$this->installation->getPackage()->packageID],
                \array_keys($this->installation->getPackage()->getAllRequiredPackages())
            ),
        ]);
        $menuItemList->sqlOrderBy = 'parentMenuItem ASC, showOrder ASC';
        $menuItemList->readObjects();

        // for better IDE auto-completion, we use `ACPMenuItem`, but the
        // menu items can also belong to other menus
        /** @var ACPMenuItem[] $menuItems */
        $menuItems = [];
        /** @var ACPMenuItem[][] $menuStructure */
        $menuStructure = [];
        foreach ($menuItemList as $menuItem) {
            if (!isset($menuStructure[$menuItem->parentMenuItem])) {
                $menuStructure[$menuItem->parentMenuItem] = [];
            }

            $menuStructure[$menuItem->parentMenuItem][$menuItem->menuItem] = $menuItem;
            $menuItems[$menuItem->menuItem] = $menuItem;
        }

        $menuItemLevels = [];
        foreach ($menuStructure as $parentMenuItemName => $childMenuItems) {
            $menuItemsLevel = 1;

            while (($parentMenuItem = $menuItems[$parentMenuItemName] ?? null)) {
                $menuItemsLevel++;
                $parentMenuItemName = $parentMenuItem->parentMenuItem;
            }

            foreach ($childMenuItems as $menuItem) {
                $menuItemLevels[$menuItem->menuItem] = $menuItemsLevel;
            }
        }

        return [
            'levels' => $menuItemLevels,
            'structure' => $menuStructure,
        ];
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'menuItem' => 'wcf.acp.pip.abstractMenu.menuItem',
            'parentMenuItem' => 'wcf.acp.pip.abstractMenu.parentMenuItem',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $formData = $form->getData()['data'];

        $menuItem = $document->createElement($this->tagName);
        $menuItem->setAttribute('name', $formData['name']);

        $this->appendElementChildren(
            $menuItem,
            [
                'controller' => '',
                'parent' => '',
                'link' => '',
                'options' => '',
                'permissions' => '',
                'showorder' => null,
            ],
            $form
        );

        return $menuItem;
    }
}
