<?php

namespace wcf\acp\form;

use wcf\acp\page\ContactOptionListPage;
use wcf\data\contact\option\ContactOption;
use wcf\http\Helper;
use wcf\system\interaction\admin\ContactOptionInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the contact option edit form.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ContactOptionEditForm extends ContactOptionAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.contact.options';

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(ContactOption::class);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new ContactOptionInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(ContactOptionListPage::class)
            ),
        ]);
    }

    #[\Override]
    protected function getContactOptions(): array
    {
        return \array_filter(
            parent::getContactOptions(),
            fn(int $key) => $key !== $this->formObject->getObjectID(),
            \ARRAY_FILTER_USE_KEY
        );
    }
}
