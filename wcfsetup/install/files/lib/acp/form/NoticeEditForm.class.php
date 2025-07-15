<?php

namespace wcf\acp\form;

use wcf\acp\page\NoticeListPage;
use wcf\data\notice\Notice;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\interaction\admin\NoticeInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Shows the form to edit an existing notice.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
    public $formAction = 'edit';

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'edit',
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new NoticeInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(NoticeListPage::class)
            ),
        ]);
    }

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (!isset($_REQUEST['id'])) {
            throw new IllegalLinkException();
        }
        $this->formObject = new Notice(\intval($_REQUEST['id']));
        if (!$this->formObject->noticeID) {
            throw new IllegalLinkException();
        }

        if ($this->formObject->isLegacy) {
            throw new NamedUserException(
                HtmlString::fromSafeHtml(WCF::getLanguage()->getDynamicVariable('wcf.acp.notice.legacyNotice')) // TODO add language item
            );
        }
    }

    #[\Override]
    public function saved()
    {
        if ($this->form->getFormField('resetIsDismissed')->getValue()) {
            $sql = "DELETE FROM wcf1_notice_dismissed
                    WHERE       noticeID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $this->formObject->noticeID,
            ]);

            UserStorageHandler::getInstance()->resetAll('dismissedNotices');
        }

        parent::saved();
    }
}
