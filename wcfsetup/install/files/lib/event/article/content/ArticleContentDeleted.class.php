<?php

namespace wcf\event\article\content;

use wcf\data\article\content\ArticleContent;
use wcf\event\IPsr14Event;

/**
 * Indicates that an article content has been deleted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ArticleContentDeleted implements IPsr14Event
{
    public function __construct(
        public readonly ArticleContent $content,
    ) {}
}
