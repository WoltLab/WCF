<?php

namespace wcf\system\cronjob;

use wcf\command\article\UpdateArticle;
use wcf\data\article\Article;
use wcf\data\article\ArticleBuilder;
use wcf\data\article\ArticleList;
use wcf\data\cronjob\Cronjob;

/**
 * Publishes delayed articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ArticlePublicationCronjob extends AbstractCronjob
{
    #[\Override]
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        $articleList = new ArticleList();
        $articleList->getConditionBuilder()->add('article.publicationStatus = ?', [Article::DELAYED_PUBLICATION]);
        $articleList->getConditionBuilder()->add('article.publicationDate > ?', [0]);
        $articleList->getConditionBuilder()->add('article.publicationDate <= ?', [\TIME_NOW]);
        $articleList->getConditionBuilder()->add('article.isDeleted = ?', [0]);
        $articleList->readObjects();

        foreach ($articleList->getObjects() as $article) {
            $builder = ArticleBuilder::forUpdate($article)
                ->setTime($article->publicationDate)
                ->setPublicationStatus(Article::PUBLISHED)
                ->setPublicationDate(0);
            new UpdateArticle($builder)();
        }
    }
}
