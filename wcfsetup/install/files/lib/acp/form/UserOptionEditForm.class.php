<?php

namespace wcf\acp\form;

use wcf\acp\page\UserOptionListPage;
use wcf\data\user\option\UserOption;
use wcf\http\Helper;
use wcf\system\form\builder\field\SelectFormField;
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

        // Options with a type that is not selectable, such as a legacy type without
        // an equivalent form option or a reserved type, keep their type, which must
        // be available to display the immutable field.
        $optionType = $this->form->getNodeById('optionType');
        \assert($optionType instanceof SelectFormField);

        if (!isset($optionType->getOptions()[$this->formObject->optionType])) {
            $formOption = $this->getAvailableFormOptions()[$this->formObject->optionType] ?? null;

            $optionType->options([
                ...$optionType->getOptions(),
                $this->formObject->optionType => $formOption?->getTitle() ?? $this->formObject->optionType,
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
