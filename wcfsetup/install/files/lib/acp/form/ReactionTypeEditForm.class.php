<?php

namespace wcf\acp\form;

use wcf\acp\page\ReactionTypeListPage;
use wcf\data\reaction\type\ReactionType;
use wcf\system\exception\IllegalLinkException;
use wcf\system\interaction\admin\ReactionTypeInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents the reaction type add form.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class ReactionTypeEditForm extends ReactionTypeAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.reactionType.list';

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->formObject = new ReactionType($_REQUEST['id']);
            if (!$this->formObject->reactionTypeID) {
                throw new IllegalLinkException();
            }
        } else {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new ReactionTypeInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(ReactionTypeListPage::class)
            ),
        ]);
    }
}
