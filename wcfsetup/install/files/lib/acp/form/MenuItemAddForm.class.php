<?php

namespace wcf\acp\form;

use wcf\data\IStorableObject;
use wcf\data\menu\item\MenuItem;
use wcf\data\menu\item\MenuItemAction;
use wcf\data\menu\item\MenuItemNode;
use wcf\data\menu\item\MenuItemNodeTree;
use wcf\data\menu\Menu;
use wcf\data\page\Page;
use wcf\data\page\PageNode;
use wcf\data\page\PageNodeTree;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\IFormDocument;
use wcf\system\page\handler\ILookupPageHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the menu item add form.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 *
 * @extends AbstractFormBuilderForm<MenuItem>
 */
class MenuItemAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.cms.canManageMenu'];

    /**
     * menu id
     */
    public int $menuID = 0;

    /**
     * menu object
     */
    public Menu $menu;

    /**
     * @var \RecursiveIteratorIterator<MenuItemNode>
     */
    public \RecursiveIteratorIterator $menuItemNodeList;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = MenuItemEditForm::class;

    /**
     * @inheritDoc
     */
    public $objectActionClass = MenuItemAction::class;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['menuID'])) {
            $this->menuID = \intval($_REQUEST['menuID']);
        }
        $this->menu = new Menu($this->menuID);
        if (!$this->menu->menuID) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->menuItemNodeList = (new MenuItemNodeTree($this->menuID, null, false))->getNodeList();
        $pageNodeList = (new PageNodeTree())->getNodeList();

        $pageHandlers = [];
        foreach ($pageNodeList as $page) {
            \assert($page instanceof PageNode);
            if ($page->getHandler() instanceof ILookupPageHandler) {
                $pageHandlers[$page->pageID] = $page->requireObjectID;
            }
        }

        $this->form->appendChildren([
            FormContainer::create('generalContainer')
                ->appendChildren([
                    SelectFormField::create('parentItemID')
                        ->label('wcf.acp.menu.item.parentItem')
                        ->options(function () {
                            $result = [];
                            foreach ($this->menuItemNodeList as $menuItem) {
                                \assert($menuItem instanceof MenuItemNode);

                                $result[] = [
                                    'depth' => $menuItem->getDepth(),
                                    'isSelectable' => $menuItem->itemID !== $this->formObject?->itemID,
                                    'label' => $menuItem->getTitle(),
                                    'value' => $menuItem->getObjectID(),
                                ];
                            }
                            return $result;
                        }, true),
                    TitleFormField::create()
                        ->i18n()
                        ->required()
                        ->languageItemPattern('wcf.menu.item.[\w\.]+'),
                    IntegerFormField::create('showOrder')
                        ->label('wcf.global.showOrder')
                        ->minimum(0)
                        ->value(0),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.menu.item.isDisabled')
                        ->value(false)
                ]),
            FormContainer::create('linkContainer')
                ->label('wcf.acp.menu.item.link')
                ->appendChildren([
                    RadioButtonFormField::create('isInternalLink')
                        ->options([
                            0 => 'wcf.acp.menu.item.link.external',
                            1 => 'wcf.acp.menu.item.link.internal',
                        ])
                        ->value(1)
                        ->required(),
                    SingleSelectionFormField::create('pageID')
                        ->label('wcf.acp.page.page')
                        ->options($pageNodeList, true)
                        ->required()
                        ->addDependency(
                            ValueFormFieldDependency::create('isInternalLinkDependency')
                                ->fieldId('isInternalLink')
                                ->values([1])
                        ),
                    $this->getPageObjectIDFormField($pageNodeList, $pageHandlers)
                        ->id('pageObjectID')
                        ->label('wcf.page.pageObjectID')
                        ->addFieldClass('short')
                        ->addValidator(
                            new FormFieldValidator('requiredObjectIDValidator', function (IntegerFormField $formField) {
                                $pageFormField = $this->form->getFormField('pageID');
                                $pageID = $pageFormField->getValue();
                                $page = new Page($pageID);
                                $pageObjectID = $formField->getValue();

                                if (!$page->pageID) {
                                    return;
                                }

                                if ($page->requireObjectID) {
                                    $pageHandler = $page->getHandler();

                                    if ($pageHandler instanceof ILookupPageHandler) {
                                        if (empty($pageObjectID)) {
                                            $formField->addValidationError(new FormFieldValidationError('empty'));
                                            return;
                                        }
                                        if (!$pageHandler->isValid($pageObjectID)) {
                                            $formField->addValidationError(
                                                new FormFieldValidationError(
                                                    'invalid',
                                                    'wcf.acp.menu.item.pageObjectID.error.invalid'
                                                )
                                            );
                                        }
                                    } elseif ($pageHandler !== null) {
                                        // page requires an object id, but no handler is registered
                                        $pageFormField->addValidationError(
                                            new FormFieldValidationError(
                                                'invalid',
                                                'wcf.acp.menu.item.pageID.error.invalid'
                                            )
                                        );
                                    }
                                }
                            })
                        )
                        ->addDependency(
                            ValueFormFieldDependency::create('isInternalLinkDependency')
                                ->fieldId('isInternalLink')
                                ->values([1])
                        )
                        ->addDependency(
                            ValueFormFieldDependency::create('pageIDDependency')
                                ->fieldId('pageID')
                                ->values(\array_keys($pageHandlers))
                        ),
                    TextFormField::create('urlParameters')
                        ->label('wcf.acp.menu.item.urlParameters')
                        ->maximumLength(255)
                        ->addDependency(
                            ValueFormFieldDependency::create('isInternalLinkDependency')
                                ->fieldId('isInternalLink')
                                ->values([1])
                        ),
                    TextFormField::create('externalURL')
                        ->label('wcf.acp.menu.item.externalURL')
                        ->maximumLength(255)
                        ->placeholder('http://')
                        ->i18n()
                        ->required()
                        ->languageItemPattern('wcf.menu.item.externalURL\d+')
                        ->addDependency(
                            ValueFormFieldDependency::create('isInternalLinkDependency')
                                ->fieldId('isInternalLink')
                                ->values([0])
                        )
                ])
        ]);
    }

    #[\Override]
    protected function finalizeForm()
    {
        parent::finalizeForm();

        $this->form
            ->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    'externalLinkDataProcessor',
                    function (IFormDocument $document, array $parameters) {
                        if ($parameters['data']['isInternalLink']) {
                            $parameters['data']['externalURL'] = '';
                        } else {
                            $parameters['data']['pageID'] = null;
                            $parameters['data']['pageObjectID'] = 0;
                        }
                        unset($parameters['data']['isInternalLink']);

                        return $parameters;
                    },
                    function (IFormDocument $document, array $data, IStorableObject $object) {
                        \assert($object instanceof MenuItem);
                        $data['isInternalLink'] = $object->pageID !== null;

                        return $data;
                    }
                )
            );
    }

    #[\Override]
    public function save()
    {
        if ($this->formAction === 'create') {
            $this->additionalFields['menuID'] = $this->menuID;
            $this->additionalFields['identifier'] = '';
            $this->additionalFields['packageID'] = PACKAGE_ID;
        }

        parent::save();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'menuID' => $this->menuID,
            'menu' => $this->menu,
            'menuItemNodeList' => $this->menuItemNodeList,
        ]);
    }

    #[\Override]
    protected function setFormAction()
    {
        $this->form->action(
            LinkHandler::getInstance()->getLink(
                'MenuItemAdd',
                [
                    'menuID' => $this->menuID,
                    'isACP' => true
                ]
            )
        );
    }

    /**
     * @param \RecursiveIteratorIterator<PageNode> $pageNodeList
     * @param array<int, int> $pageHandlers
     */
    protected function getPageObjectIDFormField(
        \RecursiveIteratorIterator $pageNodeList,
        array $pageHandlers
    ): IntegerFormField {
        return new class($pageNodeList, $pageHandlers) extends IntegerFormField {
            protected $templateName = '__pageObjectIDFormField';

            /**
             * @var array<int, int>
             */
            protected array $pageHandlers;

            /**
             * @var \RecursiveIteratorIterator<PageNode>
             */
            protected \RecursiveIteratorIterator $pageNodeList;

            /**
             * @param \RecursiveIteratorIterator<PageNode> $pageNodeList
             * @param array<int, int> $pageHandlers
             */
            public function __construct(\RecursiveIteratorIterator $pageNodeList, array $pageHandlers)
            {
                parent::__construct();
                $this->pageHandlers = $pageHandlers;
                $this->pageNodeList = $pageNodeList;
            }

            #[\Override]
            public function getHtmlVariables()
            {
                return array_merge(parent::getHtmlVariables(), [
                    'pageHandlers' => $this->pageHandlers,
                    'pageNodeList' => $this->pageNodeList,
                ]);
            }
        };
    }
}
