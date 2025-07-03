<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\acp\page\MenuListPage;
use wcf\data\menu\Menu;
use wcf\form\AbstractFormBuilderForm;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\field\TitleFormField;
use wcf\system\interaction\admin\MenuInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the menu edit form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class MenuEditForm extends MenuAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cms.menu.list';

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $this->formObject = new Menu($queryParameters['id']);

        if (!$this->formObject->getObjectID()) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    protected function createForm()
    {
        if ($this->formObject->isMainMenu()) {
            AbstractFormBuilderForm::createForm();

            $this->form->appendChildren([
                TitleFormField::create()
                    ->required()
                    ->i18n()
                    ->languageItemPattern('wcf.menu.(com.woltlab.wcf.genericMenu\d+|[\w\.]+)'),
            ]);
        } else {
            parent::createForm();
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new MenuInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(MenuListPage::class)
            ),
        ]);
    }
}
