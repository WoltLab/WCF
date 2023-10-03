<?php

namespace wcf\system\acp\dashboard\box\event;

use wcf\system\acp\dashboard\box\StatusMessage;
use wcf\system\event\IEvent;

/**
 * Requests the collection of status messages for the status message dashboard box.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class StatusMessageCollecting implements IEvent
{
    /**
     * @var StatusMessage[]
     */
    private array $messages = [];

    /**
     * Registers a new status message.
     */
    public function register(StatusMessage $message): void
    {
        $this->messages[] = $message;
    }

    /**
     * @return StatusMessage[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
