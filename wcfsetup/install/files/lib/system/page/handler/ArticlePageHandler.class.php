<?php

namespace wcf\system\page\handler;

use wcf\data\article\ViewableArticleList;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\cache\runtime\ViewableArticleContentRuntimeCache;
use wcf\system\cache\runtime\ViewableArticleRuntimeCache;
use wcf\system\WCF;

/**
 * Menu page handler for the article page.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class ArticlePageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TOnlineLocationPageHandler;

    #[\Override]
    public function getLink(int $objectID)
    {
        return ViewableArticleRuntimeCache::getInstance()->getObject($objectID)->getLink();
    }

    #[\Override]
    public function isValid(?int $objectID)
    {
        return ViewableArticleRuntimeCache::getInstance()->getObject($objectID) !== null;
    }

    #[\Override]
    public function isVisible(?int $objectID = null)
    {
        $article = ViewableArticleRuntimeCache::getInstance()->getObject($objectID);

        return $article !== null && $article->canRead();
    }

    #[\Override]
    public function lookup(string $searchString)
    {
        $articleList = new ViewableArticleList();
        $articleList->sqlSelects = "(
            SELECT  title
            FROM    wcf1_article_content
            WHERE   articleID = article.articleID
                AND (
                        languageID IS NULL
                     OR languageID = " . WCF::getLanguage()->languageID . "
                     )
            LIMIT   1
        ) AS title";
        $articleList->getConditionBuilder()->add(
            'article.articleID IN (
                SELECT  articleID
                FROM    wcf1_article_content
                WHERE   title LIKE ?
            )',
            ['%' . $searchString . '%']
        );
        $articleList->sqlLimit = 10;
        $articleList->sqlOrderBy = 'title';
        $articleList->readObjects();

        $results = [];
        foreach ($articleList->getObjects() as $article) {
            $results[] = [
                'description' => $article->getFormattedTeaser(),
                'image' => $article->getImage() ? $article->getImage()->getElementTag(48) : '',
                'link' => $article->getLink(),
                'objectID' => $article->articleID,
                'title' => $article->getTitle(),
            ];
        }

        return $results;
    }

    #[\Override]
    public function getOnlineLocation(Page $page, UserOnline $user)
    {
        if ($user->pageObjectID === null) {
            return '';
        }

        $content = ViewableArticleContentRuntimeCache::getInstance()->getObject($user->pageObjectID);
        if ($content === null || !$content->getArticle()->canRead()) {
            return '';
        }

        return WCF::getLanguage()->getDynamicVariable(
            'wcf.page.onlineLocation.' . $page->identifier,
            ['article' => $content->getArticle()]
        );
    }

    #[\Override]
    public function prepareOnlineLocation(Page $page, UserOnline $user)
    {
        if ($user->pageObjectID !== null) {
            ViewableArticleContentRuntimeCache::getInstance()->cacheObjectID($user->pageObjectID);
        }
    }

    #[\Override]
    public function cacheObject(int $objectID): void
    {
        ViewableArticleRuntimeCache::getInstance()->cacheObjectID($objectID);
    }
}
