<?php

namespace wcf\acp\form;

use wcf\acp\page\NoticeListPage;
use wcf\data\notice\Notice;
use wcf\http\Helper;
use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\interaction\admin\NoticeInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Shows the form to edit an existing notice.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class NoticeEditForm extends NoticeAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.notice.list';

    /**
     * @inheritDoc
     */
    public string $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(Notice::class);
    }

    #[\Override]
    protected function createForm(): void
    {
        parent::createForm();

        $settings = $this->form->getNodeById('settings');
        \assert($settings instanceof IFormContainer);

        $settings->appendChild(
            BooleanFormField::create('resetIsDismissed')
                ->label('wcf.acp.notice.resetIsDismissed')
                ->description('wcf.acp.notice.resetIsDismissed.description')
                ->available($this->formObject?->isDismissible !== 0)
        );
    }

    #[\Override]
    protected function afterSave(): void
    {
        parent::afterSave();

        $resetIsDismissed = $this->form->getFormField('resetIsDismissed');
        if ($resetIsDismissed === null || !$resetIsDismissed->isAvailable()) {
            return;
        }

        if ($resetIsDismissed->getSaveValue() === 0) {
            return;
        }

        $sql = "DELETE FROM wcf1_notice_dismissed
                WHERE       noticeID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->object->noticeID,
        ]);

        UserStorageHandler::getInstance()->resetAll('dismissedNotices');
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new NoticeInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(NoticeListPage::class)
            ),
        ]);
    }
}
