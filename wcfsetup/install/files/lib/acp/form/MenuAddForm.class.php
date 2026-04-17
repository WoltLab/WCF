<?php

namespace wcf\acp\form;

use wcf\data\box\Box;
use wcf\data\box\BoxAction;
use wcf\data\IStorableObject;
use wcf\data\menu\Menu;
use wcf\data\menu\MenuAction;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\acl\simple\SimpleAclHandler;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\container\TabMenuFormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\acl\simple\SimpleAclFormField;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\PagesFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\LanguageFactory;

/**
 * Shows the menu add form.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<Menu>
 */
class MenuAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.cms.canManageMenu'];

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = MenuEditForm::class;

    /**
     * @inheritDoc
     */
    public $objectActionClass = MenuAction::class;

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $tabMenu = TabMenuFormContainer::create('tabMenu');
        $tabMenu->appendChildren([
            $this->getGeneralTabContainer(),
            $this->getPagesTabContainer(),
            $this->getAclTabContainer(),
        ]);

        $this->form->appendChildren([$tabMenu]);
    }

    protected function getGeneralTabContainer(): TabFormContainer
    {
        return TabFormContainer::create('generalTab')
            ->label('wcf.global.form.data')
            ->appendChildren([
                FormContainer::create('generalContainer')
                    ->appendChildren([
                        TitleFormField::create()
                            ->required()
                            ->i18n()
                            ->languageItemPattern('wcf.menu.(com.woltlab.wcf.genericMenu\d+|[\w\.]+)'),
                        SingleSelectionFormField::create('position')
                            ->label('wcf.acp.box.position')
                            ->options(
                                \array_combine(
                                    Box::$availableMenuPositions,
                                    \array_map(function (string $postion): string {
                                        return 'wcf.acp.box.position.' . $postion;
                                    }, Box::$availableMenuPositions)
                                )
                            )
                            ->required(),
                        IntegerFormField::create('showOrder')
                            ->label('wcf.global.showOrder')
                            ->value(0)
                            ->required(),
                        TextFormField::create('cssClassName')
                            ->label('wcf.acp.box.cssClassName'),
                        BooleanFormField::create('showHeader')
                            ->label('wcf.acp.box.showHeader')
                            ->value(true),
                    ])
            ]);
    }

    #[\Override]
    public function finalizeForm()
    {
        parent::finalizeForm();

        $this->form->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    'defaultDataProcessor',
                    function (IFormDocument $document, array $parameters) {
                        $parameters['boxData'] = [];

                        if ($this->formAction === 'create') {
                            $parameters['data']['packageID'] = \PACKAGE_ID;
                            $parameters['data']['identifier'] = '';
                            $parameters['boxData']['packageID'] = \PACKAGE_ID;
                            $parameters['boxData']['boxType'] = 'menu';
                        }

                        return $parameters;
                    }
                )
            )
            ->addProcessor(
                new CustomFormDataProcessor(
                    'boxDataProcessor',
                    function (IFormDocument $document, array $parameters) {
                        if ($this->formObject?->isMainMenu()) {
                            return $parameters;
                        }

                        $parameters['boxData']['name'] = $parameters['data']['title']
                            ?? $parameters['title_i18n'][LanguageFactory::getInstance()->getDefaultLanguageID()];
                        $parameters['boxData']['cssClassName'] = $parameters['data']['cssClassName'];
                        $parameters['boxData']['showOrder'] = $parameters['data']['showOrder'];
                        $parameters['boxData']['showHeader'] = $parameters['data']['showHeader'];
                        $parameters['boxData']['visibleEverywhere'] = $parameters['data']['visibleEverywhere'];
                        $parameters['boxData']['position'] = $parameters['data']['position'];

                        unset(
                            $parameters['data']['cssClassName'],
                            $parameters['data']['showOrder'],
                            $parameters['data']['showHeader'],
                            $parameters['data']['visibleEverywhere'],
                            $parameters['data']['position']
                        );

                        return $parameters;
                    },
                    function (IFormDocument $document, array $data, IStorableObject $object) {
                        \assert($object instanceof Menu);

                        $data['position'] = $object->getBox()->position;
                        $data['cssClassName'] = $object->getBox()->cssClassName;
                        $data['showOrder'] = $object->getBox()->showOrder;
                        $data['visibleEverywhere'] = $object->getBox()->visibleEverywhere;
                        $data['pageIDs'] = $object->getBox()->getPageIDs();
                        $data['showHeader'] = $object->getBox()->showHeader;

                        $data['acl'] = SimpleAclHandler::getInstance()->getValues(
                            'com.woltlab.wcf.box',
                            $object->getBox()->boxID
                        );

                        return $data;
                    }
                )
            );
    }

    #[\Override]
    public function saved()
    {
        $formData = $this->form->getData();

        if ($this->formAction == 'create') {
            $menu = $this->objectAction->getReturnValues()['returnValues'];
            \assert($menu instanceof Menu);
        } else {
            $menu = new Menu($this->formObject->menuID);
        }

        if ($this->formAction !== 'create' && !$menu->isMainMenu()) {
            $formData['data'] = $formData['boxData'];
            unset($formData['boxData']);

            $boxAction = new BoxAction([$menu->getBox()->boxID], 'update', $formData);
            $boxAction->executeAction();
        }

        if (!$menu->isMainMenu()) {
            SimpleAclHandler::getInstance()->setValues(
                'com.woltlab.wcf.box',
                $menu->getBox()->boxID,
                $formData['acl']
            );
        }

        parent::saved();
    }

    protected function getPagesTabContainer(): TabFormContainer
    {
        return TabFormContainer::create('pagesTab')
            ->label('wcf.acp.page.list')
            ->appendChildren([
                FormContainer::create('pagesContainer')
                    ->appendChildren([
                        BooleanFormField::create('visibleEverywhere')
                            ->label('wcf.acp.box.visibleEverywhere')
                            ->value(true),
                        PagesFormField::create()
                            ->visibleEverywhereFieldId('visibleEverywhere')
                    ])
            ]);
    }

    protected function getAclTabContainer(): TabFormContainer
    {
        return TabFormContainer::create('aclTab')
            ->label('wcf.acl.access')
            ->appendChildren([
                FormContainer::create('aclContainer')
                    ->appendChildren([
                        SimpleAclFormField::create('acl')
                    ])
            ]);
    }
}
