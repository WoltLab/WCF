<?php

namespace wcf\event\message;

use wcf\data\IMessage;
use wcf\event\IPsr14Event;

/**
 * Requests the `IMessage` object associated with the provided object id that
 * will be used to render a quote.
 *
 * The object MUST NOT be set if the user does not have access.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class MessageQuoteRendering implements IPsr14Event
{
    private IMessage $message;

    public function __construct(
        public readonly string $objectType,
        public readonly int $objectID,
    ) {}

    public function setMessage(IMessage $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): ?IMessage
    {
        return $this->message ?? null;
    }
}
