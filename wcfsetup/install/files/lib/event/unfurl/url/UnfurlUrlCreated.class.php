<?php

namespace wcf\event\unfurl\url;

use wcf\data\unfurl\url\UnfurlUrl;
use wcf\data\unfurl\url\UnfurlUrlBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that an unfurl url has been created.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UnfurlUrlCreated implements IPsr14Event
{
    public function __construct(
        public readonly UnfurlUrl $unfurlUrl,
        public readonly UnfurlUrlBuilder $builder,
    ) {}
}
