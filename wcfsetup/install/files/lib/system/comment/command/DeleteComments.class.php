<?php

namespace wcf\system\comment\command;

use wcf\data\comment\Comment;

/**
 * Deletes a bunch of comments that belong to the same object type.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\comment\DeleteComments` instead.
 */
final class DeleteComments
{
    /**
     * @param Comment[] $comments
     */
    public function __construct(
        private readonly array $comments,
        private readonly bool $updateCounters = true,
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\comment\DeleteComments($this->comments, $this->updateCounters))();
    }
}
