<?php

namespace wcf\system\comment\response\command;

use wcf\data\comment\response\CommentResponse;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Updates a comment response.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\comment\response\UpdateResponse` instead.
 */
final class UpdateResponse
{
    public function __construct(
        private readonly CommentResponse $response,
        private readonly HtmlInputProcessor $htmlInputProcessor,
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\comment\response\UpdateResponse(
            $this->response,
            $this->htmlInputProcessor
        ))();
    }
}
