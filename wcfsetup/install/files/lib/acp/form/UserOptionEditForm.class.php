<?php

namespace wcf\acp\form;

use wcf\acp\page\UserOptionListPage;
use wcf\data\user\option\UserOption;
use wcf\http\Helper;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\interaction\admin\UserOptionInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the user option edit form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserOptionEditForm extends UserOptionAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.option.list';

    /**
     * @inheritDoc
     */
    public string $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(UserOption::class);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        if ($this->formObject->optionName === 'aboutMe') {
            $optionType = $this->form->getNodeById('optionType');
            \assert($optionType instanceof SingleSelectionFormField);

            $optionType->options([
                ...$optionType->getOptions(),
                'aboutMe' => 'aboutMe',
            ]);
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new UserOptionInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(UserOptionListPage::class)
            ),
        ]);
    }
}
