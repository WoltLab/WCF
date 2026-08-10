<?php

namespace wcf\data\user;

use wcf\system\edit\EditHistoryManager;
use wcf\system\WCF;

/**
 * Executes actions on user generated content.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserContentAction extends UserAction
{
    /**
     * Checks permissions to bulk revert.
     *
     * @return void
     */
    public function validateBulkRevert()
    {
        WCF::getSession()->checkPermissions(['admin.content.canBulkRevertContentChanges']);
    }

    /**
     * Bulk reverts changes made to history saving objects.
     *
     * @return void
     */
    public function bulkRevert()
    {
        $this->readInteger('timeframe', true);
        if ($this->parameters['timeframe'] === 0) {
            $this->parameters['timeframe'] = 86400 * 7;
        }

        EditHistoryManager::getInstance()->bulkRevert($this->objectIDs, $this->parameters['timeframe']);
    }
}
