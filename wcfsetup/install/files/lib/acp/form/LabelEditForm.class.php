<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\acp\page\LabelListPage;
use wcf\data\label\Label;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\interaction\admin\LabelInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the label edit form.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LabelEditForm extends LabelAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.label.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.label.canManageLabel'];

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

        $this->formObject = new Label($queryParameters['id']);

        if (!$this->formObject->getObjectID()) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new LabelInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(LabelListPage::class)
            ),
        ]);
    }
}
