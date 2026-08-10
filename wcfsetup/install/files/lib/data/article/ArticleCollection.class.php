<?php

namespace wcf\data\article;

use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentList;
use wcf\data\DatabaseObjectCollection;
use wcf\data\TCollectionLabels;
use wcf\data\TCollectionReactions;
use wcf\data\TCollectionUserProfiles;
use wcf\data\TCollectionVisitTimes;
use wcf\system\article\discussion\IArticleDiscussionProvider;
use wcf\system\label\object\ArticleLabelObjectHandler;
use wcf\system\WCF;

/**
 * Represents a collection of articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<Article>
 */
class ArticleCollection extends DatabaseObjectCollection
{
    use TCollectionReactions;
    use TCollectionUserProfiles;
    use TCollectionLabels;
    use TCollectionVisitTimes;

    /**
     * @var array<int, array<int, ArticleContent>>
     */
    private array $articleContents;

    /**
     * @var array<int, IArticleDiscussionProvider>
     */
    private array $discussionProviders;

    public function getArticleContent(Article $article): ?ArticleContent
    {
        $this->loadArticleContents();

        if (
            $article->getActiveLanguageID() !== null
            && isset($this->articleContents[$article->getObjectID()][$article->getActiveLanguageID()])
        ) {
            return $this->articleContents[$article->getObjectID()][$article->getActiveLanguageID()];
        }
        if (isset($this->articleContents[$article->getObjectID()][WCF::getLanguage()->languageID])) {
            return $this->articleContents[$article->getObjectID()][WCF::getLanguage()->languageID];
        }
        if (isset($this->articleContents[$article->getObjectID()][0])) {
            return $this->articleContents[$article->getObjectID()][0];
        }
        if (isset($this->articleContents[$article->getObjectID()]) && $this->articleContents[$article->getObjectID()] !== []) {
            return \reset($this->articleContents[$article->getObjectID()]);
        }

        return null;
    }

    /**
     * @return array<int, ArticleContent>
     */
    public function getArticleContents(Article $article): array
    {
        $this->loadArticleContents();

        return $this->articleContents[$article->getObjectID()] ?? [];
    }

    private function loadArticleContents(): void
    {
        if (isset($this->articleContents)) {
            return;
        }

        $this->articleContents = [];

        $contentList = new ArticleContentList();
        $contentList->getConditionBuilder()->add('article_content.articleID IN (?)', [$this->getObjectIDs()]);
        $contentList->readObjects();
        foreach ($contentList->getObjects() as $articleContent) {
            $this->articleContents[$articleContent->articleID][$articleContent->languageID ?: 0] = $articleContent;
        }
    }

    public function getDiscussionProvider(Article $article): ?IArticleDiscussionProvider
    {
        $this->loadDiscussionProviders();

        return $this->discussionProviders[$article->getObjectID()] ?? null;
    }

    private function loadDiscussionProviders(): void
    {
        if (isset($this->discussionProviders)) {
            return;
        }

        $this->discussionProviders = [];

        foreach ($this->getObjects() as $object) {
            foreach (Article::getAllDiscussionProviders() as $discussionProvider) {
                if (\call_user_func([$discussionProvider, 'isResponsible'], $object) === true) {
                    $this->discussionProviders[$object->getObjectID()] = new $discussionProvider($object);
                    break;
                }
            }
        }
    }

    #[\Override]
    protected function getReactionObjectType(): string
    {
        return 'com.woltlab.wcf.likeableArticle';
    }

    #[\Override]
    protected function getLabelObjectHandler(): ArticleLabelObjectHandler
    {
        return ArticleLabelObjectHandler::getInstance();
    }

    #[\Override]
    protected function getVisitTrackerObjectType(): string
    {
        return 'com.woltlab.wcf.article';
    }
}
