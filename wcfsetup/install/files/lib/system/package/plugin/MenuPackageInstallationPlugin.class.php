<?php

namespace wcf\system\package\plugin;

use wcf\data\box\Box;
use wcf\data\box\BoxEditor;
use wcf\data\menu\Menu;
use wcf\data\menu\MenuEditor;
use wcf\data\menu\MenuList;
use wcf\data\page\PageNode;
use wcf\data\page\PageNodeTree;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\devtools\pip\IDevtoolsPipEntryList;
use wcf\system\devtools\pip\IGuiPackageInstallationPlugin;
use wcf\system\devtools\pip\TXmlGuiPackageInstallationPlugin;
use wcf\system\exception\SystemException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\NonEmptyFormFieldDependency;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\ItemListFormField;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\field\validation\FormFieldValidatorUtil;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Installs, updates and deletes menus.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class MenuPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin implements
    IGuiPackageInstallationPlugin,
    IUniqueNameXMLPackageInstallationPlugin
{
    use TXmlGuiPackageInstallationPlugin;

    /**
     * box meta data per menu
     * @var array
     */
    public $boxData = [];

    /**
     * visibility exceptions per box
     * @var string[]
     */
    public $visibilityExceptions = [];

    /**
     * @inheritDoc
     */
    public $className = MenuEditor::class;

    /**
     * @inheritDoc
     */
    public $tagName = 'menu';

    /**
     * @inheritDoc
     */
    protected function handleDelete(array $items)
    {
        $sql = "DELETE FROM wcf1_menu
                WHERE       identifier = ?
                        AND packageID = ?";
        $statement = WCF::getDB()->prepare($sql);

        $sql = "DELETE FROM wcf1_language_item
                WHERE       languageItem = ?";
        $languageItemStatement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($items as $item) {
            $statement->execute([
                $item['attributes']['identifier'],
                $this->installation->getPackageID(),
            ]);

            $languageItemStatement->execute([
                'wcf.menu.' . $item['attributes']['identifier'],
            ]);
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * @inheritDoc
     * @throws  SystemException
     */
    protected function getElement(\DOMXPath $xpath, array &$elements, \DOMElement $element)
    {
        $nodeValue = $element->nodeValue;

        if ($element->tagName === 'title') {
            if (empty($element->getAttribute('language'))) {
                throw new SystemException("Missing required attribute 'language' for menu '" . $element->parentNode->getAttribute('identifier') . "'");
            }

            // <title> can occur multiple times using the `language` attribute
            if (!isset($elements['title'])) {
                $elements['title'] = [];
            }

            $elements['title'][$element->getAttribute('language')] = $element->nodeValue;
        } elseif ($element->tagName === 'box') {
            $elements['box'] = [];

            /** @var \DOMElement $child */
            foreach ($xpath->query('child::*', $element) as $child) {
                if ($child->tagName === 'name') {
                    if (empty($child->getAttribute('language'))) {
                        throw new SystemException("Missing required attribute 'language' for box name (menu '" . $element->parentNode->getAttribute('identifier') . "')");
                    }

                    // <title> can occur multiple times using the `language` attribute
                    if (!isset($elements['box']['name'])) {
                        $elements['box']['name'] = [];
                    }

                    $elements['box']['name'][$element->getAttribute('language')] = $element->nodeValue;
                } elseif ($child->tagName === 'visibilityExceptions') {
                    $elements['box']['visibilityExceptions'] = [];
                    /** @var \DOMElement $child */
                    foreach ($xpath->query('child::*', $child) as $child2) {
                        $elements['box']['visibilityExceptions'][] = $child2->nodeValue;
                    }
                } else {
                    $elements['box'][$child->tagName] = $child->nodeValue;
                }
            }
        } else {
            $elements[$element->tagName] = $nodeValue;
        }
    }

    /**
     * @inheritDoc
     */
    protected function prepareImport(array $data)
    {
        $identifier = $data['attributes']['identifier'];

        if (!empty($data['elements']['box'])) {
            $position = $data['elements']['box']['position'];

            if ($identifier === 'com.woltlab.wcf.MainMenu') {
                $position = 'mainMenu';
            } elseif (!\in_array($position, Box::$availableMenuPositions)) {
                throw new SystemException("Unknown box position '{$position}' for menu box '{$identifier}'");
            }

            $this->boxData[$identifier] = [
                'identifier' => $identifier,
                'name' => $this->getI18nValues(
                    !empty($data['elements']['box']['name']) ? $data['elements']['box']['name'] : $data['elements']['title'],
                    true
                ),
                'boxType' => 'menu',
                'position' => $position,
                'showHeader' => !empty($data['elements']['box']['showHeader']) ? 1 : 0,
                'visibleEverywhere' => !empty($data['elements']['box']['visibleEverywhere']) ? 1 : 0,
                'cssClassName' => (!empty($data['elements']['box']['cssClassName'])) ? $data['elements']['box']['cssClassName'] : '',
                'originIsSystem' => 1,
                'packageID' => $this->installation->getPackageID(),
            ];

            if (!empty($data['elements']['box']['visibilityExceptions'])) {
                $this->visibilityExceptions[$identifier] = $data['elements']['box']['visibilityExceptions'];
            }

            unset($data['elements']['box']);
        }

        return [
            'identifier' => $identifier,
            'title' => $this->getI18nValues($data['elements']['title']),
            'originIsSystem' => 1,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getNameByData(array $data): string
    {
        return $data['identifier'];
    }

    /**
     * @inheritDoc
     */
    protected function findExistingItem(array $data)
    {
        $sql = "SELECT  *
                FROM    wcf1_menu
                WHERE   identifier = ?
                    AND packageID = ?";
        $parameters = [
            $data['identifier'],
            $this->installation->getPackageID(),
        ];

        return [
            'sql' => $sql,
            'parameters' => $parameters,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function import(array $row, array $data)
    {
        // updating menus is not supported because the only modifiable data is the
        // title and overwriting it could conflict with user changes
        if (!empty($row)) {
            return new Menu(null, $row);
        }

        return parent::import($row, $data);
    }

    /**
     * @inheritDoc
     */
    protected function postImport()
    {
        if (empty($this->boxData)) {
            return;
        }

        // all boxes belonging to the identifiers
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("identifier IN (?)", [\array_keys($this->boxData)]);
        $conditions->add("packageID = ?", [$this->installation->getPackageID()]);

        $sql = "SELECT  *
                FROM    wcf1_box
                {$conditions}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        /** @var Box[] $boxes */
        $boxes = $statement->fetchObjects(Box::class, 'identifier');

        // fetch all menus relevant
        $menuList = new MenuList();
        $menuList->getConditionBuilder()->add('identifier IN (?)', [\array_keys($this->boxData)]);
        $menuList->readObjects();

        $menus = [];
        foreach ($menuList as $menu) {
            $menus[$menu->identifier] = $menu;
        }

        // handle visibility exceptions
        $sql = "DELETE FROM wcf1_box_to_page
                WHERE       boxID = ?";
        $deleteStatement = WCF::getDB()->prepare($sql);
        $sql = "INSERT IGNORE   wcf1_box_to_page
                                (boxID, pageID, visible)
                VALUES          (?, ?, ?)";
        $insertStatement = WCF::getDB()->prepare($sql);
        foreach ($this->boxData as $identifier => $data) {
            // connect box with menu
            if (isset($menus[$identifier])) {
                $data['menuID'] = $menus[$identifier]->menuID;
            }

            $box = null;
            if (isset($boxes[$identifier])) {
                $box = $boxes[$identifier];

                // delete old visibility exceptions
                $deleteStatement->execute([$box->boxID]);

                // skip both 'identifier' and 'packageID' as these properties are immutable
                unset($data['identifier']);
                unset($data['packageID']);

                $boxEditor = new BoxEditor($box);
                $boxEditor->update($data);
            } else {
                $box = BoxEditor::create($data);
            }

            // save visibility exceptions
            if (!empty($this->visibilityExceptions[$identifier])) {
                // get page ids
                $conditionBuilder = new PreparedStatementConditionBuilder();
                $conditionBuilder->add('identifier IN (?)', [$this->visibilityExceptions[$identifier]]);
                $sql = "SELECT  pageID
                        FROM    wcf1_page
                        {$conditionBuilder}";
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditionBuilder->getParameters());
                $pageIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

                // save page ids
                foreach ($pageIDs as $pageID) {
                    $insertStatement->execute([$box->boxID, $pageID, $box->visibleEverywhere ? 0 : 1]);
                }
            }
        }
    }

    /**
     * @inheritDoc
     * @since   3.1
     */
    public static function getSyncDependencies()
    {
        return ['language'];
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getAdditionalTemplateCode()
    {
        return WCF::getTPL()->fetch('__menuPipGui');
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function addFormFields(IFormDocument $form)
    {
        /** @var FormContainer $dataContainer */
        $dataContainer = $form->getNodeById('data');

        $dataContainer->appendChildren([
            TextFormField::create('identifier')
                ->label('wcf.acp.pip.menu.identifier')
                ->description('wcf.acp.pip.menu.identifier.description')
                ->required()
                ->addValidator(FormFieldValidatorUtil::getDotSeparatedStringValidator(
                    'wcf.acp.pip.menu.identifier',
                    4
                ))
                ->addValidator(new FormFieldValidator('uniqueness', function (TextFormField $formField) {
                    if (
                        $formField->getDocument()->getFormMode() === IFormDocument::FORM_MODE_CREATE
                        || $this->editedEntry->getAttribute('identifier') !== $formField->getValue()
                    ) {
                        $menuList = new MenuList();
                        $menuList->getConditionBuilder()->add('identifier = ?', [$formField->getValue()]);

                        if ($menuList->countObjects() > 0) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'notUnique',
                                    'wcf.acp.pip.menu.identifier.error.notUnique'
                                )
                            );
                        }
                    }
                })),

            TitleFormField::create()
                ->required()
                ->i18n()
                ->i18nRequired()
                ->languageItemPattern('__NONE__'),

            BooleanFormField::create('createBox')
                ->label('wcf.acp.pip.menu.createBox')
                ->description('wcf.acp.pip.menu.createBox.description'),

            SingleSelectionFormField::create('boxPosition')
                ->label('wcf.acp.pip.menu.boxPosition')
                ->description('wcf.acp.pip.menu.boxPosition.description')
                ->options(\array_combine(Box::$availablePositions, Box::$availablePositions))
                ->addDependency(
                    ValueFormFieldDependency::create('identifier')
                        ->fieldId('identifier')
                        ->values(['com.woltlab.wcf.MainMenu'])
                        ->negate()
                ),

            BooleanFormField::create('boxShowHeader')
                ->label('wcf.acp.pip.menu.boxShowHeader'),

            BooleanFormField::create('boxVisibleEverywhere')
                ->label('wcf.acp.pip.menu.boxVisibleEverywhere'),

            MultipleSelectionFormField::create('boxVisibilityExceptions')
                ->label('wcf.acp.pip.menu.boxVisibilityExceptions.hiddenEverywhere')
                ->filterable()
                ->options(static function () {
                    $pageNodeList = (new PageNodeTree())->getNodeList();

                    $nestedOptions = [];
                    /** @var PageNode $pageNode */
                    foreach ($pageNodeList as $pageNode) {
                        $nestedOptions[] = [
                            'depth' => $pageNode->getDepth() - 1,
                            'label' => $pageNode->name,
                            'value' => $pageNode->identifier,
                        ];
                    }

                    return $nestedOptions;
                }, true),

            ItemListFormField::create('boxCssClassName')
                ->label('wcf.acp.pip.menu.boxCssClassName')
                ->description('wcf.acp.pip.menu.boxCssClassName.description')
                ->saveValueType(ItemListFormField::SAVE_VALUE_TYPE_SSV),
        ]);

        /** @var BooleanFormField $createBox */
        $createBox = $form->getNodeById('createBox');
        foreach (
            [
                'boxPosition',
                'boxShowHeader',
                'boxVisibleEverywhere',
                'boxVisibilityExceptions',
                'boxCssClassName',
            ] as $boxField
        ) {
            $form->getNodeById($boxField)->addDependency(
                NonEmptyFormFieldDependency::create('createBox')
                    ->field($createBox)
            );
        }
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function fetchElementData(\DOMElement $element, $saveData)
    {
        $data = [
            'identifier' => $element->getAttribute('identifier'),
            'packageID' => $this->installation->getPackageID(),
            'title' => [],
        ];

        /** @var \DOMElement $title */
        foreach ($element->getElementsByTagName('title') as $title) {
            $data['title'][LanguageFactory::getInstance()->getLanguageByCode($title->getAttribute('language'))->languageID] = $title->nodeValue;
        }

        $box = $element->getElementsByTagName('box')->item(0);
        $boxData = [];
        if ($box !== null) {
            $boxData['position'] = $box->getElementsByTagName('position')->item(0)->nodeValue;

            // work-around for unofficial position `mainMenu`
            if ($data['identifier'] === 'com.woltlab.wcf.MainMenu' && !$saveData) {
                unset($boxData['position']);
            }

            $showHeader = $element->getElementsByTagName('showHeader')->item(0);
            if ($showHeader !== null) {
                $boxData['showHeader'] = $showHeader->nodeValue;
            }

            $visibleEverywhere = $element->getElementsByTagName('visibleEverywhere')->item(0);
            if ($visibleEverywhere !== null) {
                $boxData['visibleEverywhere'] = $visibleEverywhere->nodeValue;
            }

            $cssClassName = $element->getElementsByTagName('cssClassName')->item(0);
            if ($cssClassName !== null) {
                $boxData['cssClassName'] = $cssClassName->nodeValue;
            }

            $visibilityExceptions = $element->getElementsByTagName('visibilityExceptions');
            if ($visibilityExceptions->length > 0) {
                $boxData['visibilityExceptions'] = [];

                /** @var \DOMElement $visibilityException */
                foreach ($visibilityExceptions as $visibilityException) {
                    $boxData['visibilityExceptions'] = $visibilityException->nodeValue;
                }
            }
        }

        if ($saveData) {
            if (!empty($boxData)) {
                $this->boxData[$data['identifier']] = [
                    'identifier' => $data['identifier'],
                    'name' => $this->getI18nValues($data['title'], true),
                    'boxType' => 'menu',
                    'position' => $boxData['position'],
                    'showHeader' => $boxData['showHeader'] ?? 0,
                    'visibleEverywhere' => $boxData['visibleEverywhere'] ?? 0,
                    'cssClassName' => $boxData['cssClassName'] ?? '',
                    'originIsSystem' => 1,
                    'packageID' => $this->installation->getPackageID(),
                ];

                if (!empty($boxData['visibilityExceptions'])) {
                    $this->visibilityExceptions[$data['identifier']] = $boxData['visibilityExceptions'];
                }
            }

            // update menus is not supported thus handling the title
            // array causes issues
            if ($this->editedEntry !== null) {
                unset($data['title']);
            } else {
                $titles = [];
                foreach ($data['title'] as $languageID => $title) {
                    $titles[LanguageFactory::getInstance()->getLanguage($languageID)->languageCode] = $title;
                }

                $data['title'] = $titles;
            }
        } else {
            $data['createBox'] = $box !== null;

            foreach ($boxData as $key => $value) {
                $data['box' . \ucfirst($key)] = $value;
            }
        }

        return $data;
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function getElementIdentifier(\DOMElement $element)
    {
        return $element->getAttribute('identifier');
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function setEntryListKeys(IDevtoolsPipEntryList $entryList)
    {
        $entryList->setKeys([
            'identifier' => 'wcf.acp.pip.menu.identifier',
        ]);
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    protected function prepareXmlElement(\DOMDocument $document, IFormDocument $form)
    {
        $formData = $form->getData();

        if ($formData['data']['identifier'] === 'com.woltlab.wcf.MainMenu') {
            $formData['data']['boxPosition'] = 'mainMenu';
        }

        $menu = $document->createElement($this->tagName);
        $menu->setAttribute('identifier', $formData['data']['identifier']);

        foreach ($formData['title_i18n'] as $languageID => $title) {
            $title = $document->createElement('title', $this->getAutoCdataValue($title));
            $title->setAttribute('language', LanguageFactory::getInstance()->getLanguage($languageID)->languageCode);

            $menu->appendChild($title);
        }

        if ($formData['data']['createBox']) {
            $box = $document->createElement('box');

            $box->appendChild($document->createElement('position', $formData['data']['boxPosition']));

            foreach (
                [
                    'showHeader' => 0,
                    'visibleEverywhere' => 0,
                    'cssClassName' => '',
                ] as $boxProperty => $defaultValue
            ) {
                $index = 'box' . \ucfirst($boxProperty);
                if (isset($formData['data'][$index]) && $formData['data'][$index] !== $defaultValue) {
                    $box->appendChild($document->createElement($boxProperty, (string)$formData['data'][$index]));
                }
            }

            if (!empty($formData['boxVisibilityExceptions'])) {
                $visibilityExceptions = $box->appendChild($document->createElement('visibilityExceptions'));

                \sort($formData['boxVisibilityExceptions']);
                foreach ($formData['boxVisibilityExceptions'] as $pageIdentifier) {
                    $visibilityExceptions->appendChild($document->createElement('page', $pageIdentifier));
                }
            }

            $menu->appendChild($box);
        }

        return $menu;
    }
}
