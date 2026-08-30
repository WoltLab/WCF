<?php

namespace wcf\command\comment\response;

use wcf\data\comment\response\CommentResponseBuilder;
use wcf\event\comment\response\ResponseUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates a comment response.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UpdateResponse
{
    public function __construct(
        private readonly CommentResponseBuilder $builder,
    ) {}

    public function __invoke(): void
    {
        $response = $this->builder->update();

        EventHandler::getInstance()->fire(new ResponseUpdated($response, $this->builder));
    }
}
