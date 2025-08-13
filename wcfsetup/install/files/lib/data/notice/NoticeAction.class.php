<?php

namespace wcf\data\notice;

use wcf\command\notice\DisableNotice;
use wcf\command\notice\EnableNotice;
use wcf\command\notice\DismissNotice;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\condition\ConditionHandler;

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
     *
     * @deprecated 6.3 Use the `DismissNotice` command instead.
     */
    public function dismiss()
    {
        $editor = $this->getSingleObject();

        if ($editor->isDismissible) {
            (new DismissNotice($editor->getDecoratedObject()))();
        }

        return [
            'noticeID' => \reset($this->objectIDs),
        ];
    }

    /**
     * Validates the 'dismiss' action.
     *
     * @return void
     *
     * @deprecated 6.3
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

    /**
     * @deprecated 6.3
     */
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * @deprecated 6.3 use the `EnableNotice` or `DisableNotice` commands instead.
     */
    public function toggle()
    {
        foreach ($this->objects as $editor) {
            if ($editor->isDisabled) {
                (new EnableNotice($editor->getDecoratedObject()))();
            } else {
                (new DisableNotice($editor->getDecoratedObject()))();
            }
        }
    }
}
