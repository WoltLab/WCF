<?php

namespace wcf\system\message\embedded\object;

use wcf\data\article\AccessibleArticleList;
use wcf\data\article\Article;
use wcf\system\cache\runtime\ArticleRuntimeCache;
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
    #[\Override]
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

    #[\Override]
    public function loadObjects(array $objectIDs)
    {
        return ArticleRuntimeCache::getInstance()->getObjects($objectIDs);
    }

    #[\Override]
    public function validateValues(string $objectType, int $objectID, array $values)
    {
        $articleList = new AccessibleArticleList();
        $articleList->getConditionBuilder()->add('article.articleID IN (?)', [$values]);
        $articleList->readObjects();
        $articles = $articleList->getObjects();

        return \array_filter($values, static function ($value) use ($articles) {
            return isset($articles[$value]);
        });
    }

    #[\Override]
    public function replaceSimple(string $objectType, int $objectID, string|int $value, array $attributes)
    {
        $article = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.article', $value);
        if ($article === null) {
            return null;
        }

        \assert($article instanceof Article);

        $return = (!empty($attributes['return'])) ? $attributes['return'] : 'link';
        switch ($return) {
            case 'title':
                return $article->getTitle();

            case 'link':
            default:
                return $article->getLink();
        }
    }
}
