<?php

namespace wcf\event\comment\response;

use wcf\data\comment\response\CommentResponse;
use wcf\event\IPsr14Event;

/**
 * Indicates that a response has been updated.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ResponseUpdated implements IPsr14Event
{
    public function __construct(
        public readonly CommentResponse $comment,
    ) {
    }
}
