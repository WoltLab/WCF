<?php

namespace wcf\acp\form;

use wcf\acp\page\TrophyListPage;
use wcf\data\trophy\Trophy;
use wcf\system\exception\IllegalLinkException;
use wcf\system\interaction\admin\TrophyInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents the trophy edit form.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
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
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        if (!isset($_REQUEST['id'])) {
            throw new IllegalLinkException();
        }

        $this->formObject = new Trophy(\intval($_REQUEST['id']));

        if (!$this->formObject->trophyID) {
            throw new IllegalLinkException();
        }

        parent::readParameters();
    }

    /**
     * @inheritDoc
     */
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
