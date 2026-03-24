<?php

namespace wcf\system\log\modification;

/**
 * Extended modification log handler implementation for modification log handlers
 * that do not support their logs being shown in the global ACP modification log.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class VoidExtendedModificationLogHandler extends AbstractExtendedModificationLogHandler
{
    #[\Override]
    public function getAvailableActions()
    {
        return [];
    }

    #[\Override]
    public function includeInLogList()
    {
        return false;
    }

    #[\Override]
    public function processItems(array $items)
    {
        throw new \BadMethodCallException("Cannot process items.");
    }
}
