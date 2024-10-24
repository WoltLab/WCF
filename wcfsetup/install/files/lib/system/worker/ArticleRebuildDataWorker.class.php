<?php

namespace wcf\system\worker;

use wcf\data\article\ArticleEditor;
use wcf\data\article\ArticleList;
use wcf\data\article\content\ArticleContentEditor;
use wcf\data\article\content\ArticleContentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;

/**
 * Worker implementation for updating articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method  ArticleList getObjectList()
 */
class ArticleRebuildDataWorker extends AbstractRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = ArticleList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 100;

    /**
     * @var HtmlInputProcessor
     */
    protected $htmlInputProcessor;

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlOrderBy = 'article.articleID';
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        if (!$this->loopCount) {
            // reset search index
            SearchIndexManager::getInstance()->reset('com.woltlab.wcf.article');
        }

        if (!\count($this->objectList)) {
            return;
        }
        $articles = $this->objectList->getObjects();

        $commentObjectType = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.comment.commentableContent', 'com.woltlab.wcf.articleComment');
        $sql = "SELECT  COUNT(*) AS comments, SUM(responses) AS responses
                FROM    wcf1_comment
                WHERE   objectTypeID = ?
                    AND objectID = ?";
        $commentStatement = WCF::getDB()->prepare($sql);
        $comments = [];

        // update article content
        $articleContentList = new ArticleContentList();
        $articleContentList->getConditionBuilder()->add(
            'article_content.articleID IN (?)',
            [$this->objectList->getObjectIDs()]
        );
        $articleContentList->readObjects();
        foreach ($articleContentList as $articleContent) {
            $data = [];

            // count comments
            $commentStatement->execute([$commentObjectType->objectTypeID, $articleContent->articleContentID]);
            $row = $commentStatement->fetchSingleRow();
            $data['comments'] = $row['comments'] + $row['responses'];

            // update search index
            SearchIndexManager::getInstance()->set(
                'com.woltlab.wcf.article',
                $articleContent->articleContentID,
                $articleContent->content,
                $articleContent->title,
                $articles[$articleContent->articleID]->time,
                $articles[$articleContent->articleID]->userID,
                $articles[$articleContent->articleID]->username,
                $articleContent->languageID,
                $articleContent->teaser
            );

            // update embedded objects
            $this->getHtmlInputProcessor()->processEmbeddedContent(
                $articleContent->content,
                'com.woltlab.wcf.article.content',
                $articleContent->articleContentID
            );

            $hasEmbeddedObjects = 0;
            if (MessageEmbeddedObjectManager::getInstance()->registerObjects($this->getHtmlInputProcessor())) {
                $hasEmbeddedObjects = 1;
            }

            if ($hasEmbeddedObjects != $articleContent->hasEmbeddedObjects) {
                $data['hasEmbeddedObjects'] = $hasEmbeddedObjects;
            }

            $articleContentEditor = new ArticleContentEditor($articleContent);
            $articleContentEditor->update($data);
        }

        // fetch cumulative likes
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("objectTypeID = ?", [
            ObjectTypeCache::getInstance()
                ->getObjectTypeIDByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.likeableArticle'),
        ]);
        $conditions->add("objectID IN (?)", [$this->objectList->getObjectIDs()]);

        $sql = "SELECT  objectID, cumulativeLikes
                FROM    wcf1_like_object
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $cumulativeLikes = $statement->fetchMap('objectID', 'cumulativeLikes');

        foreach ($this->objectList as $article) {
            $editor = new ArticleEditor($article);
            $data = [];

            // update cumulative likes
            $data['cumulativeLikes'] = $cumulativeLikes[$article->articleID] ?? 0;

            // update data
            $editor->update($data);
        }
    }

    /**
     * @return HtmlInputProcessor
     */
    protected function getHtmlInputProcessor()
    {
        if ($this->htmlInputProcessor === null) {
            $this->htmlInputProcessor = new HtmlInputProcessor();
        }

        return $this->htmlInputProcessor;
    }
}
