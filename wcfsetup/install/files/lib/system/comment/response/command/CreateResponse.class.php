<?php

namespace wcf\system\comment\response\command;

use wcf\data\comment\Comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseBuilder;
use wcf\data\user\User;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Creates a new comment response.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\comment\response\CreateResponse` instead.
 */
final class CreateResponse
{
    public function __construct(
        private readonly Comment $comment,
        private readonly HtmlInputProcessor $htmlInputProcessor,
        private readonly ?User $user = null,
        private readonly string $username = '',
        private readonly bool $isDisabled = false,
    ) {}

    public function __invoke(): CommentResponse
    {
        $builder = CommentResponseBuilder::forCreate()
            ->setComment($this->comment)
            ->setTime(\TIME_NOW)
            ->setHtmlInputProcessor($this->htmlInputProcessor)
            ->setIsDisabled($this->isDisabled);

        if ($this->user !== null) {
            $builder->setUser($this->user);
        } else {
            $builder->setGuest($this->username);
        }

        return new \wcf\command\comment\response\CreateResponse($builder)();
    }
}
