<?php

namespace wcf\system\bulk\processing;

use wcf\data\DatabaseObjectList;
use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a bulk processing action.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @template TDatabaseObjectList of DatabaseObjectList
 * @implements IBulkProcessingAction<TDatabaseObjectList>
 */
abstract class AbstractBulkProcessingAction extends AbstractObjectTypeProcessor implements IBulkProcessingAction
{
    #[\Override]
    public function getHTML()
    {
        return '';
    }

    #[\Override]
    public function isAvailable()
    {
        return true;
    }

    #[\Override]
    public function readFormParameters()
    {
        // does nothing
    }

    #[\Override]
    public function reset()
    {
        // does nothing
    }

    #[\Override]
    public function validate()
    {
        // does nothing
    }

    #[\Override]
    public function canRunInWorker(): bool
    {
        return false;
    }

    #[\Override]
    public function getAdditionalParameters(): array
    {
        return [];
    }

    #[\Override]
    public function loadAdditionalParameters(array $data): void
    {
        // does nothing by default
    }
}
