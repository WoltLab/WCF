<?php

namespace wcf\event\acp\dashboard\box;

use wcf\event\IPsr14Event;
use wcf\system\acp\dashboard\box\StatusMessage;

/**
 * Requests the collection of status messages for the status message dashboard box.
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class StatusMessageCollecting implements IPsr14Event
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
