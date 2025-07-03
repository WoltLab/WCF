<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\acp\page\LanguageListPage;
use wcf\data\language\Language;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\interaction\admin\LanguageInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the language edit form.
 *
 * @author  Florian Gail
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LanguageEditForm extends LanguageAddForm
{
    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
            $this->formObject = new Language($queryParameters['id']);

            if (!$this->formObject->getObjectID()) {
                throw new IllegalLinkException();
            }
        } catch (MappingError) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->getFormField('isDisabled')->available(!$this->formObject->isDefault);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new LanguageInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(LanguageListPage::class)
            ),
        ]);
    }
}
