<?php

namespace wcf\acp\form;

use wcf\acp\page\UserTrophyListPage;
use wcf\data\user\trophy\UserTrophy;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\interaction\admin\UserTrophyInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents the user trophy edit form.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserTrophyEditForm extends UserTrophyAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.userTrophy.list';

    /**
     * @inheritDoc
     */
    public string $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(UserTrophy::class);

        // The description of automatically awarded trophies cannot be changed.
        if ($this->formObject->getTrophy()->awardAutomatically !== 0) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new UserTrophyInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(UserTrophyListPage::class)
            ),
        ]);
    }
}
