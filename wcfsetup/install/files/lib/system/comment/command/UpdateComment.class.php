<?php

namespace wcf\system\comment\command;

use wcf\data\comment\Comment;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Updates a comment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\comment\UpdateComment` instead.
 */
final class UpdateComment
{
    public function __construct(
        private readonly Comment $comment,
        private readonly HtmlInputProcessor $htmlInputProcessor,
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\comment\UpdateComment($this->comment, $this->htmlInputProcessor))();
    }
}
