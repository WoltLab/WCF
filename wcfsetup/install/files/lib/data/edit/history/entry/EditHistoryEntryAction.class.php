<?php

namespace wcf\data\edit\history\entry;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\IllegalLinkException;

/**
 * Executes edit history entry-related actions.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `EditHistoryEntryBuilder` instead.
 *
 * @extends AbstractDatabaseObjectAction<EditHistoryEntry, EditHistoryEntryEditor>
 */
class EditHistoryEntryAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = EditHistoryEntryEditor::class;

    /**
     * Checks permissions to revert.
     *
     * @return void
     * @deprecated  6.3 Use the RPC endpoint `POST /core/edit-history-entries/{id}/revert` instead.
     */
    public function validateRevert()
    {
        if (\MODULE_EDIT_HISTORY === 0) {
            throw new IllegalLinkException();
        }

        $historyEntry = $this->getSingleObject();

        $objectType = ObjectTypeCache::getInstance()->getObjectType($historyEntry->objectTypeID);
        $processor = $objectType->getProcessor();
        $object = $this->getSingleObject()->getObject();
        $processor->checkPermissions($object);
    }

    /**
     * Reverts the objects back to this history entry.
     *
     * @return void
     * @deprecated  6.3 Use the RPC endpoint `POST /core/edit-history-entries/{id}/revert` instead.
     */
    public function revert()
    {
        $this->getSingleObject()->getObject()->revertVersion($this->getSingleObject()->getDecoratedObject());
    }
}
