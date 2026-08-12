<?php

namespace wcf\acp\form;

use wcf\acp\page\CronjobListPage;
use wcf\data\cronjob\Cronjob;
use wcf\http\Helper;
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

        $this->formObject = Helper::fetchObjectFromQueryParameter(Cronjob::class);
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
