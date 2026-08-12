<?php

namespace wcf\acp\form;

use wcf\acp\page\TemplateGroupListPage;
use wcf\data\template\group\TemplateGroup;
use wcf\http\Helper;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\interaction\admin\TemplateGroupInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the form for editing template groups.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TemplateGroupEditForm extends TemplateGroupAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.template.group.list';

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(TemplateGroup::class);

        if ($this->formObject->isImmutable()) {
            throw new PermissionDeniedException();
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new TemplateGroupInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(TemplateGroupListPage::class)
            ),
        ]);
    }
}
