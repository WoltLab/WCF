<?php

namespace wcf\event\article;

use wcf\data\article\Article;
use wcf\event\IPsr14Event;

/**
 * Indicates that an article has been flagged as featured.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ArticleSetAsFeatured implements IPsr14Event
{
    public function __construct(public readonly Article $article)
    {
    }
}
