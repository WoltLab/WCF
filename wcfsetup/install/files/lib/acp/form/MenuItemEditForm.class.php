<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\acp\page\MenuItemListPage;
use wcf\data\menu\item\MenuItem;
use wcf\data\menu\Menu;
use wcf\form\AbstractFormBuilderForm;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\interaction\admin\MenuItemInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the menu item edit form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MenuItemEditForm extends MenuItemAddForm
{
    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        AbstractFormBuilderForm::readParameters();

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
            $this->formObject = new MenuItem($queryParameters['id']);

            if ($this->formObject->getObjectID() === 0) {
                throw new IllegalLinkException();
            }
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $this->menuID = $this->formObject->menuID;
        $this->menu = new Menu($this->menuID);
    }

    #[\Override]
    protected function setFormAction()
    {
        AbstractFormBuilderForm::setFormAction();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new MenuItemInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(
                    MenuItemListPage::class,
                    ['id' => $this->menuID]
                )
            ),
        ]);
    }
}
