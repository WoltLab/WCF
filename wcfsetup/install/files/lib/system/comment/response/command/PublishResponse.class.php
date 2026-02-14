<?php

namespace wcf\system\comment\response\command;

use wcf\data\comment\response\CommentResponse;

/**
 * Publishes a comment response.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\comment\response\PublishResponse` instead.
 */
final class PublishResponse
{
    public function __construct(
        private readonly CommentResponse $response,
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\comment\response\PublishResponse(
            $this->response,
        ))();
    }
}
