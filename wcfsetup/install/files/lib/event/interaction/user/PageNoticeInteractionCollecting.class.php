<?php

namespace wcf\event\interaction\user;

use wcf\event\IPsr14Event;
use wcf\system\interaction\user\PageNoticeInteractions;

/**
 * Indicates that the provider for page notices is collecting interactions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PageNoticeInteractionCollecting implements IPsr14Event
{
    public function __construct(public readonly PageNoticeInteractions $provider) {}
}
