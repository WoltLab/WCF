<?php

namespace wcf\page;

use wcf\data\article\AccessibleArticleList;
use wcf\data\article\ArticleAction;
use wcf\data\article\ArticleEditor;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\ViewableArticleContent;
use wcf\data\article\ViewableArticle;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\tag\Tag;
use wcf\system\cache\runtime\ViewableArticleRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\page\PageLocationManager;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;

/**
 * Abstract implementation of the article page.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
abstract class AbstractArticlePage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_ARTICLE'];

    /**
     * article content id
     * @var int
     */
    public $articleContentID = 0;

    /**
     * article content object
     * @var ViewableArticleContent
     */
    public $articleContent;

    /**
     * article object
     * @var ViewableArticle
     */
    public $article;

    /**
     * list of tags
     * @var Tag[]
     */
    public $tags = [];

    /**
     * category object
     * @var ArticleCategory
     */
    public $category;

    /**
     * list of related articles
     * @var AccessibleArticleList
     */
    public $relatedArticles;

    /**
     * @var GroupedAttachmentList
     */
    public $attachmentList;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->articleContentID = \intval($_REQUEST['id']);
        }
        $this->articleContent = ViewableArticleContent::getArticleContent($this->articleContentID);
        if ($this->articleContent === null) {
            throw new IllegalLinkException();
        }

        // check if the language has been disabled
        if ($this->articleContent->languageID && LanguageFactory::getInstance()->getLanguage($this->articleContent->languageID) === null) {
            throw new IllegalLinkException();
        }

        $this->article = ViewableArticleRuntimeCache::getInstance()->getObject($this->articleContent->articleID);
        $this->article->getDiscussionProvider()->setArticleContent($this->articleContent->getDecoratedObject());
        $this->category = $this->article->getCategory();

        if (!$this->article->canRead()) {
            throw new PermissionDeniedException();
        }

        // update interface language
        if (!WCF::getUser()->userID && $this->article->isMultilingual && $this->articleContent->languageID != WCF::getLanguage()->languageID) {
            WCF::setLanguage($this->articleContent->languageID);
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // update view count
        if ($this->article->isPublished()) {
            $articleEditor = new ArticleEditor($this->article->getDecoratedObject());
            $articleEditor->updateCounters([
                'views' => 1,
            ]);
        }

        // update article visit
        if ($this->article->isNew()) {
            $articleAction = new ArticleAction([$this->article->getDecoratedObject()], 'markAsRead', [
                'viewableArticle' => $this->article,
            ]);
            $articleAction->executeAction();
        }

        // get tags
        if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag')) {
            $this->tags = TagEngine::getInstance()->getObjectTags(
                'com.woltlab.wcf.article',
                $this->articleContent->articleContentID,
                [$this->articleContent->languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()]
            );
        }

        // get related articles
        if (MODULE_TAGGING && ARTICLE_RELATED_ARTICLES) {
            if (!empty($this->tags)) {
                $conditionBuilder = new PreparedStatementConditionBuilder();
                $conditionBuilder->add(
                    'tag_to_object.objectTypeID = ?',
                    [TagEngine::getInstance()->getObjectTypeID('com.woltlab.wcf.article')]
                );
                $conditionBuilder->add('tag_to_object.tagID IN (?)', [\array_keys($this->tags)]);
                $conditionBuilder->add('tag_to_object.objectID <> ?', [$this->articleContentID]);
                $sql = "SELECT      MAX(article.articleID), COUNT(*) AS count
                        FROM        wcf1_tag_to_object tag_to_object
                        INNER JOIN  wcf1_article_content article_content
                        ON          tag_to_object.objectID = article_content.articleContentID
                        INNER JOIN  wcf1_article article
                        ON          article_content.articleID = article.articleID
                        " . $conditionBuilder . "
                        GROUP BY    tag_to_object.objectID
                        HAVING      COUNT(*) >= " . \round(\count($this->tags) * ARTICLE_RELATED_ARTICLES_MATCH_THRESHOLD / 100) . "
                        ORDER BY    count DESC, MAX(article.time) DESC";
                $statement = WCF::getDB()->prepare($sql, ARTICLE_RELATED_ARTICLES * 4);
                $statement->execute($conditionBuilder->getParameters());
                $articleIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

                if (!empty($articleIDs)) {
                    if (\count($articleIDs) > ARTICLE_RELATED_ARTICLES) {
                        \shuffle($articleIDs);
                        $articleIDs = \array_slice($articleIDs, 0, ARTICLE_RELATED_ARTICLES);
                    }

                    $this->relatedArticles = new AccessibleArticleList();
                    $this->relatedArticles->getConditionBuilder()->add('article.articleID IN (?)', [$articleIDs]);
                    $this->relatedArticles->sqlOrderBy = 'article.time';
                    $this->relatedArticles->readObjects();
                }
            }
        }

        // set location
        PageLocationManager::getInstance()->addParentLocation(
            'com.woltlab.wcf.CategoryArticleList',
            $this->article->categoryID,
            $this->article->getCategory()
        );
        foreach (\array_reverse($this->article->getCategory()->getParentCategories()) as $parentCategory) {
            PageLocationManager::getInstance()->addParentLocation(
                'com.woltlab.wcf.CategoryArticleList',
                $parentCategory->categoryID,
                $parentCategory
            );
        }

        // get attachments
        $this->attachmentList = $this->article->getAttachments();
        $this->filterEmbeddedAttachments();
        MessageEmbeddedObjectManager::getInstance()
            ->setActiveMessage('com.woltlab.wcf.article.content', $this->articleContentID);
    }

    /**
     * Filters attachments embedded in the article's description from the normal listing.
     * @since   6.0
     */
    protected function filterEmbeddedAttachments(): void
    {
        if ($this->attachmentList !== null && !empty($this->attachmentList->getObjects())) {
            $sql = "SELECT  embeddedObjectID
                    FROM    wcf1_message_embedded_object
                    WHERE   messageObjectTypeID = ?
                        AND messageID IN (
                            SELECT  articleContentID
                            FROM    wcf1_article_content
                            WHERE   articleID = ?
                        )
                        AND embeddedObjectTypeID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                ObjectTypeCache::getInstance()
                    ->getObjectTypeIDByName('com.woltlab.wcf.message', 'com.woltlab.wcf.article.content'),
                $this->article->articleID,
                ObjectTypeCache::getInstance()
                    ->getObjectTypeIDByName('com.woltlab.wcf.message.embeddedObject', 'com.woltlab.wcf.attachment'),
            ]);
            $attachmentIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($attachmentIDs as $attachmentID) {
                if (isset($this->attachmentList->getObjects()[$attachmentID])) {
                    $this->attachmentList->getObjects()[$attachmentID]->markAsEmbedded();
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'articleContentID' => $this->articleContentID,
            'articleContent' => $this->articleContent,
            'article' => $this->article,
            'category' => $this->category,
            'relatedArticles' => $this->relatedArticles,
            'tags' => $this->tags,
            'attachmentList' => $this->attachmentList,
        ]);
    }
}
