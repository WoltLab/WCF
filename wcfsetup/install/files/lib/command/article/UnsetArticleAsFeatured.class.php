<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleBuilder;
use wcf\event\article\ArticleUnsetAsFeatured;
use wcf\system\event\EventHandler;

/**
 * Removes the featured flag from an article.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UnsetArticleAsFeatured
{
    public function __construct(private readonly Article $article) {}

    public function __invoke(): void
    {
        ArticleBuilder::forUpdate($this->article)
            ->setIsFeatured(false)
            ->update();

        $event = new ArticleUnsetAsFeatured($this->article);
        EventHandler::getInstance()->fire($event);
    }
}
