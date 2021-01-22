<?php

namespace wcf\system\log\modification;

use wcf\data\modification\log\IViewableModificationLog;
use wcf\data\modification\log\ModificationLog;

/**
 * Common interface for modification log handlers that support item processing for
 * display in the global modification log.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Log\Modification
 * @since       5.2
 */
interface IExtendedModificationLogHandler
{
    /**
     * Returns the list of possible actions for this object type.
     *
     * @return string[]
     */
    public function getAvailableActions();

    /**
     * Returns `true` if logs handled by this handler may be included in the
     * global ACP log list and returns `false` otherwise.
     *
     * @return  bool
     */
    public function includeInLogList();

    /**
     * Processes a list of items by converting them into IViewableModificationLog
     * instances and pre-loading their data.
     *
     * @param ModificationLog[] $items
     * @return  IViewableModificationLog[]
     */
    public function processItems(array $items);
}
