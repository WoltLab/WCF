<?php

namespace wcf\event\comment\response;

use wcf\data\comment\response\CommentResponse;
use wcf\event\IPsr14Event;

/**
 * Indicates that multiple responses are about to be deleted.
 * Fired before any data is removed, allowing the cleanup of linked data.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ResponsesDeleting implements IPsr14Event
{
    /**
     * @param CommentResponse[] $responses
     */
    public function __construct(
        public readonly array $responses,
    ) {}
}
