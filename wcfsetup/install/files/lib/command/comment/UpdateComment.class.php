<?php

namespace wcf\command\comment;

use wcf\data\comment\CommentBuilder;
use wcf\event\comment\CommentUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates a comment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UpdateComment
{
    public function __construct(
        private readonly CommentBuilder $builder,
    ) {}

    public function __invoke(): void
    {
        $comment = $this->builder->update();

        EventHandler::getInstance()->fire(new CommentUpdated($comment, $this->builder));
    }
}
