<?php

namespace wcf\system\comment\command;

use wcf\data\comment\Comment;
use wcf\data\object\type\ObjectType;
use wcf\data\user\User;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Creates a new comment.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\comment\CreateComment` instead.
 */
final class CreateComment
{
    public function __construct(
        private readonly ObjectType $objectType,
        private readonly int $objectID,
        private readonly HtmlInputProcessor $htmlInputProcessor,
        private readonly ?User $user = null,
        private readonly string $username = '',
        private readonly bool $isDisabled = false,
    ) {}

    public function __invoke(): Comment
    {
        return (new \wcf\command\comment\CreateComment(
            $this->objectType,
            $this->objectID,
            $this->htmlInputProcessor,
            $this->user,
            $this->username,
            $this->isDisabled
        ))();
    }
}
