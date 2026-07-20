<?php

namespace wcf\event\tag;

use wcf\data\tag\Tag;
use wcf\data\tag\TagBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a tag has been created.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class TagCreated implements IPsr14Event
{
    public function __construct(
        public readonly Tag $tag,
        public readonly TagBuilder $builder,
    ) {}
}
