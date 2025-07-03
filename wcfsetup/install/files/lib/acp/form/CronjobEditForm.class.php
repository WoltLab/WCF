<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\acp\page\CronjobListPage;
use wcf\data\cronjob\Cronjob;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\interaction\admin\CronjobInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows the cronjob edit form.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CronjobEditForm extends CronjobAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.cronjob.list';

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
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $this->formObject = new Cronjob($queryParameters['id']);

        if (!$this->formObject->getObjectID()) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->getFormField('isDisabled')->available($this->formObject->canBeDisabled());
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new CronjobInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(CronjobListPage::class)
            ),
        ]);
    }
}
