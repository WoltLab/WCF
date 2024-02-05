<?php

namespace wcf\system\rssFeed;

/**
 * Represents an rss feed enclosure.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class RssFeedEnclosure
{
    public function __construct(
        public readonly string $url,
        public readonly int $length,
        public readonly string $type
    ) {
    }
}
