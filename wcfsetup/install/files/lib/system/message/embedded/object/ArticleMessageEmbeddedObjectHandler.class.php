<?php

namespace wcf\system\message\embedded\object;

use wcf\data\article\AccessibleArticleList;
use wcf\data\article\Article;
use wcf\data\article\content\ViewableArticleContentList;
use wcf\data\article\ViewableArticleList;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Parses embedded articles and outputs their link or title.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ArticleMessageEmbeddedObjectHandler extends AbstractSimpleMessageEmbeddedObjectHandler
{
    /**
     * @inheritDoc
     */
    public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData)
    {
        $articleIDs = [];
        if (!empty($embeddedData['wsa'])) {
            for ($i = 0, $length = \count($embeddedData['wsa']); $i < $length; $i++) {
                $articleIDs[] = \intval($embeddedData['wsa'][$i][0]);
            }
        }

        return \array_unique($articleIDs);
    }

    /**
     * @inheritDoc
     */
    public function loadObjects(array $objectIDs)
    {
        // Do not use `ViewableArticleRuntimeCache` to avoid recursively loading embedded objects.
        $articleList = new ViewableArticleList();
        $articleList->enableEmbeddedObjectLoading(false);
        $articleList->getConditionBuilder()->add('article.articleID IN (?)', [$objectIDs]);
        $articleList->readObjects();
        $articles = $articleList->getObjects();

        $contentLanguageID = MessageEmbeddedObjectManager::getInstance()->getContentLanguageID();
        if ($contentLanguageID !== null) {
            $articleIDs = [];
            foreach ($articles as $article) {
                if (
                    $article !== null
                    && $article->getArticleContent()->languageID
                    && $article->getArticleContent()->languageID != $contentLanguageID
                ) {
                    $articleIDs[] = $article->articleID;
                }
            }

            if (!empty($articleIDs)) {
                $list = new ViewableArticleContentList();
                $list->getConditionBuilder()->add("articleID IN (?)", [$articleIDs]);
                $list->getConditionBuilder()->add("languageID = ?", [$contentLanguageID]);
                $list->readObjects();

                foreach ($list->getObjects() as $articleContent) {
                    $articles[$articleContent->articleID]->setArticleContent($articleContent);
                }
            }
        }

        return $articles;
    }

    /**
     * @inheritDoc
     */
    public function validateValues($objectType, $objectID, array $values)
    {
        $articleList = new AccessibleArticleList();
        $articleList->getConditionBuilder()->add('article.articleID IN (?)', [$values]);
        $articleList->readObjects();
        $articles = $articleList->getObjects();

        return \array_filter($values, static function ($value) use ($articles) {
            return isset($articles[$value]);
        });
    }

    /**
     * @inheritDoc
     */
    public function replaceSimple($objectType, $objectID, $value, array $attributes)
    {
        /** @var Article $article */
        $article = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.article', $value);
        if ($article === null) {
            return;
        }

        $return = (!empty($attributes['return'])) ? $attributes['return'] : 'link';
        switch ($return) {
            case 'title':
                return $article->getTitle();
                break;

            case 'link':
            default:
                return $article->getLink();
                break;
        }
    }
}
