<?php

namespace wcf\acp\form;

use wcf\acp\page\UserOptionListPage;
use wcf\data\user\option\UserOption;
use wcf\form\AbstractFormBuilderForm;
use wcf\http\Helper;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\option\FormOptionHandler;
use wcf\system\interaction\admin\UserOptionInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\language\I18nHandler;
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
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(UserOption::class);
    }

    #[\Override]
    public function createForm()
    {
        parent::createForm();

        // Options with a legacy type that has no equivalent form option keep their
        // type, which must be available to display the immutable field.
        if (FormOptionHandler::getInstance()->getOption($this->formObject->optionType) === null) {
            $optionType = $this->form->getNodeById('optionType');
            \assert($optionType instanceof SelectFormField);

            $optionType->options([
                ...$optionType->getOptions(),
                $this->formObject->optionType => $this->formObject->optionType,
            ]);
        }
    }

    #[\Override]
    public function saved()
    {
        I18nHandler::getInstance()->save(
            'optionName',
            'wcf.user.option.' . $this->formObject->optionName,
            'wcf.user.option'
        );
        I18nHandler::getInstance()->save(
            'optionDescription',
            'wcf.user.option.' . $this->formObject->optionName . '.description',
            'wcf.user.option'
        );

        AbstractFormBuilderForm::saved();
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
