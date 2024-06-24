<?php

namespace wcf\event\comment\response;

use wcf\data\comment\response\CommentResponse;
use wcf\event\IPsr14Event;

/**
 * Indicates that a new comment response has been published. This can happen directly when a comment is created
 * or be delayed if a response has first been checked and approved by a moderator.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ResponsePublished implements IPsr14Event
{
    public function __construct(
        public readonly CommentResponse $response,
    ) {
    }
}
