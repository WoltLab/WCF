<?php

namespace wcf\acp\form;

use wcf\acp\page\BBCodeMediaProviderListPage;
use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\http\Helper;
use wcf\system\interaction\admin\BBCodeMediaProviderInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the BBCode media provider edit form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class BBCodeMediaProviderEditForm extends BBCodeMediaProviderAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.bbcode.mediaProvider.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.bbcode.canManageBBCode'];

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(BBCodeMediaProvider::class);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new BBCodeMediaProviderInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(BBCodeMediaProviderListPage::class)
            ),
        ]);
    }
}
