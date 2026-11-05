<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleBuilder;
use wcf\event\article\ArticleSetAsFeatured;
use wcf\system\event\EventHandler;

/**
 * Flags an article as featured.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class SetArticleAsFeatured
{
    public function __construct(private readonly Article $article) {}

    public function __invoke(): void
    {
        ArticleBuilder::forUpdate($this->article)
            ->setIsFeatured(true)
            ->update();

        $event = new ArticleSetAsFeatured($this->article);
        EventHandler::getInstance()->fire($event);
    }
}
