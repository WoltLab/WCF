<?php

namespace wcf\system\clipboard\action;

use wcf\data\clipboard\action\ClipboardAction;
use wcf\data\user\UserContentAction;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for edit history entries.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserContentClipboardAction extends AbstractClipboardAction
{
    /**
     * @inheritDoc
     */
    protected $supportedActions = ['revertContentChanges'];

    /**
     * @inheritDoc
     */
    public function execute(array $objects, ClipboardAction $action)
    {
        $item = parent::execute($objects, $action);

        if ($item === null) {
            return;
        }

        // handle actions
        switch ($action->actionName) {
            case 'revertContentChanges':
                $item->setURL(LinkHandler::getInstance()->getLink('UserContentRevertChanges'));
                break;
        }

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getClassName()
    {
        return UserContentAction::class;
    }

    /**
     * @inheritDoc
     */
    public function getTypeName()
    {
        return 'com.woltlab.wcf.user';
    }

    /**
     * Returns the ids of the users whose edits can be reverted.
     *
     * @return  int[]
     */
    protected function validateRevertContentChanges()
    {
        if (!MODULE_EDIT_HISTORY) {
            return [];
        }

        // check permissions
        if (!WCF::getSession()->getPermission('admin.content.canBulkRevertContentChanges')) {
            return [];
        }

        $userIDs = [];
        foreach ($this->objects as $user) {
            $userIDs[] = $user->userID;
        }

        return $userIDs;
    }
}
