<?php

namespace wcf\system\comment\command;

use wcf\data\comment\Comment;

/**
 * Publishes a comment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\comment\PublishComment` instead.
 */
final class PublishComment
{
    public function __construct(
        private readonly Comment $comment,
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\comment\PublishComment($this->comment))();
    }
}
