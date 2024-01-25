<?php

namespace wcf\system\bulk\processing;

use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a bulk processing action.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
abstract class AbstractBulkProcessingAction extends AbstractObjectTypeProcessor implements IBulkProcessingAction
{
    /**
     * @inheritDoc
     */
    public function getHTML()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
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
