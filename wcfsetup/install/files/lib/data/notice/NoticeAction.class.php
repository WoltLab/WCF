<?php

namespace wcf\data\notice;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
use wcf\data\TI18nDatabaseObjectAction;
use wcf\system\condition\ConditionHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes notice-related actions.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Notice, NoticeEditor>
 */
class NoticeAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    use TDatabaseObjectToggle;
    use TI18nDatabaseObjectAction;

    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['dismiss'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.notice.canManageNotice'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.notice.canManageNotice'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'toggle', 'update', 'updatePosition'];

    /**
     * @inheritDoc
     * @return  Notice
     */
    public function create()
    {
        $showOrder = 0;
        if (isset($this->parameters['data']['showOrder'])) {
            $showOrder = $this->parameters['data']['showOrder'];
            unset($this->parameters['data']['showOrder']);
        }

        /** @var Notice $notice */
        $notice = parent::create();

        $this->saveI18nValue($notice);

        $noticeEditor = new NoticeEditor($notice);
        $noticeEditor->setShowOrder($showOrder);

        return new Notice($notice->noticeID);
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        ConditionHandler::getInstance()->deleteConditions('com.woltlab.wcf.condition.notice', $this->objectIDs);

        $count = parent::delete();

        $this->deleteI18nValues();

        return $count;
    }

    /**
     * Dismisses a certain notice.
     *
     * @return  int[]
     */
    public function dismiss()
    {
        if (WCF::getUser()->userID) {
            $sql = "INSERT IGNORE INTO  wcf1_notice_dismissed
                                        (noticeID, userID)
                    VALUES              (?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                \reset($this->objectIDs),
                WCF::getUser()->userID,
            ]);

            UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'dismissedNotices');
        } else {
            $dismissedNotices = WCF::getSession()->getVar('dismissedNotices');
            if ($dismissedNotices !== null) {
                $dismissedNotices = @\unserialize($dismissedNotices);
                $dismissedNotices[] = \reset($this->objectIDs);
            } else {
                $dismissedNotices = [
                    \reset($this->objectIDs),
                ];
            }

            WCF::getSession()->register('dismissedNotices', \serialize($dismissedNotices));
        }

        return [
            'noticeID' => \reset($this->objectIDs),
        ];
    }

    /**
     * Validates the 'dismiss' action.
     *
     * @return void
     */
    public function validateDismiss()
    {
        $this->getSingleObject();
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        parent::update();

        foreach ($this->getObjects() as $labelEditor) {
            $this->saveI18nValue($labelEditor->getDecoratedObject());
        }

        if (
            \count($this->objects) == 1
            && isset($this->parameters['data']['showOrder'])
            && $this->parameters['data']['showOrder'] != \reset($this->objects)->showOrder
        ) {
            \reset($this->objects)->setShowOrder($this->parameters['data']['showOrder']);
        }
    }

    #[\Override]
    public function getI18nSaveTypes(): array
    {
        return ['notice' => 'wcf.notice.notice.notice\d+'];
    }

    #[\Override]
    public function getLanguageCategory(): string
    {
        return 'wcf.notice';
    }

    #[\Override]
    public function getPackageID(): int
    {
        return 1;
    }
}
