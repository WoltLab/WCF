<?php

namespace wcf\acp\form;

use wcf\acp\page\TrophyListPage;
use wcf\data\trophy\Trophy;
use wcf\http\Helper;
use wcf\system\interaction\admin\TrophyInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents the trophy edit form.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TrophyEditForm extends TrophyAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.trophy.list';

    /**
     * @inheritDoc
     */
    public string $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(Trophy::class);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new TrophyInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(TrophyListPage::class)
            ),
        ]);
    }
}
