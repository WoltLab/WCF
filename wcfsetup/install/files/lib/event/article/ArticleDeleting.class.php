<?php

namespace wcf\event\article;

use wcf\data\article\Article;
use wcf\event\IPsr14Event;

/**
 * Indicates that an article is about to be deleted permanently.
 * Fired before any data is removed, allowing the cleanup of linked data.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ArticleDeleting implements IPsr14Event
{
    public function __construct(
        public readonly Article $article,
    ) {}
}
