<?php

namespace wcf\data\notice;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;
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

        return parent::delete();
    }

    /**
     * Dismisses a certain notice.
     *
     * @return  int[]
     */
    public function dismiss()
    {
        // The validated object is used instead of the request parameter, because
        // `getSingleObject()` only requires that exactly one of the submitted ids
        // resolves to an existing notice, not that only one id was submitted.
        $noticeID = $this->getSingleObject()->noticeID;

        if (WCF::getUser()->userID) {
            $sql = "INSERT IGNORE INTO  wcf1_notice_dismissed
                                        (noticeID, userID)
                    VALUES              (?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $noticeID,
                WCF::getUser()->userID,
            ]);

            UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'dismissedNotices');
        } else {
            $dismissedNotices = [];

            $sessionValue = WCF::getSession()->getVar('dismissedNotices');
            if ($sessionValue !== null) {
                $dismissedNotices = @\unserialize($sessionValue);
                if (!\is_array($dismissedNotices)) {
                    $dismissedNotices = [];
                }
            }

            // Skipping the update for an already dismissed notice keeps repeated
            // requests from growing the session variables without bounds.
            if (!\in_array($noticeID, $dismissedNotices, true)) {
                $dismissedNotices[] = $noticeID;

                WCF::getSession()->register('dismissedNotices', \serialize($dismissedNotices));
            }
        }

        return [
            'noticeID' => $noticeID,
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

        if (
            \count($this->objects) == 1
            && isset($this->parameters['data']['showOrder'])
            && $this->parameters['data']['showOrder'] != \reset($this->objects)->showOrder
        ) {
            \reset($this->objects)->setShowOrder($this->parameters['data']['showOrder']);
        }
    }
}
