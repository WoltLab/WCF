<?php

namespace wcf\system\listView\user;

use wcf\data\article\AccessibleArticleList;
use wcf\system\cache\runtime\ViewableArticleContentRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;

/**
 * List view for the list of related articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class RelatedArticleListView extends ArticleListView
{
    public function __construct(public readonly int $articleContentID)
    {
        parent::__construct();

        $this->setAllowFiltering(false);
        $this->setAllowSorting(false);
        $this->setAllowInteractions(false);
    }

    #[\Override]
    protected function createObjectList(): AccessibleArticleList
    {
        $articleIDs = $this->getRelatedArticleIDs($this->articleContentID);
        $list = parent::createObjectList();

        if ($articleIDs !== []) {
            if (\count($articleIDs) > ARTICLE_RELATED_ARTICLES) {
                \shuffle($articleIDs);
                $articleIDs = \array_slice($articleIDs, 0, ARTICLE_RELATED_ARTICLES);
            }

            $list->getConditionBuilder()->add('article.articleID IN (?)', [$articleIDs]);
        } else {
            $list->getConditionBuilder()->add('1 = 0');
        }

        return $list;
    }

    #[\Override]
    public function getParameters(): array
    {
        return ['articleContentID' => $this->articleContentID];
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return parent::isAccessible()
            && \MODULE_TAGGING
            && \ARTICLE_RELATED_ARTICLES
            && WCF::getSession()->getPermission('user.tag.canViewTag');
    }

    /**
     * @return array<int>
     */
    private function getRelatedArticleIDs(int $articleContentID): array
    {
        $articleContent = ViewableArticleContentRuntimeCache::getInstance()->getObject($articleContentID);

        $tags = TagEngine::getInstance()->getObjectTags(
            'com.woltlab.wcf.article',
            $articleContentID,
            [$articleContent->languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()]
        );

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add(
            'tag_to_object.objectTypeID = ?',
            [TagEngine::getInstance()->getObjectTypeID('com.woltlab.wcf.article')]
        );
        $conditionBuilder->add('tag_to_object.tagID IN (?)', [\array_keys($tags)]);
        $conditionBuilder->add('tag_to_object.objectID <> ?', [$articleContentID]);
        $sql = "SELECT      MAX(article.articleID), COUNT(*) AS count
                FROM        wcf1_tag_to_object tag_to_object
                INNER JOIN  wcf1_article_content article_content
                ON          tag_to_object.objectID = article_content.articleContentID
                INNER JOIN  wcf1_article article
                ON          article_content.articleID = article.articleID
                " . $conditionBuilder . "
                GROUP BY    tag_to_object.objectID
                HAVING      COUNT(*) >= " . \round(\count($tags) * ARTICLE_RELATED_ARTICLES_MATCH_THRESHOLD / 100) . "
                ORDER BY    count DESC, MAX(article.time) DESC";
        $statement = WCF::getDB()->prepare($sql, ARTICLE_RELATED_ARTICLES * 4);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
