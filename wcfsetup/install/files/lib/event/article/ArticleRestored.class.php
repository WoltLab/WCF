<?php

namespace wcf\event\article;

use wcf\data\article\Article;
use wcf\event\IPsr14Event;

/**
 * Indicates that an article has been restored.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ArticleRestored implements IPsr14Event
{
    public function __construct(public readonly Article $article)
    {
    }
}
