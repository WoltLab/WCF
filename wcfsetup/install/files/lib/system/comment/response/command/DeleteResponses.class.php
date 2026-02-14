<?php

namespace wcf\system\comment\response\command;

use wcf\data\comment\response\CommentResponse;

/**
 * Deletes a bunch of comment responses that belong to the same object type.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\comment\response\DeleteResponses` instead.
 */
final class DeleteResponses
{
    /**
     * @param CommentResponse[] $responses
     */
    public function __construct(
        private readonly array $responses,
        private readonly bool $updateCounters = true,
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\comment\response\DeleteResponses(
            $this->responses,
            $this->updateCounters
        ))();
    }
}
