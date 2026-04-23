<?php

namespace wcf\page;

use wcf\command\article\MarkArticleAsRead;
use wcf\data\article\Article;
use wcf\data\article\ArticleEditor;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\CategoryArticleList;
use wcf\data\article\content\ArticleContent;
use wcf\data\tag\Tag;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\interaction\user\ArticleInteractions;
use wcf\system\language\LanguageFactory;
use wcf\system\listView\user\RelatedArticleListView;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\MetaTagHandler;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a cms article.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ArticlePage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_ARTICLE'];

    public ArticleContent $articleContent;
    public ?Article $article = null;
    public ?ArticleCategory $category = null;
    public RelatedArticleListView $relatedArticleListView;
    public ?Article $nextArticle = null;
    public ?Article $previousArticle = null;

    /**
     * @var Tag[]
     */
    public array $tags = [];

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        $this->articleContent = Helper::fetchObjectFromQueryParameter(ArticleContent::class);

        // check if the language has been disabled
        if ($this->articleContent->languageID && LanguageFactory::getInstance()->getLanguage($this->articleContent->languageID) === null) {
            throw new IllegalLinkException();
        }

        $this->article = $this->articleContent->getArticle();
        $this->article->getDiscussionProvider()->setArticleContent($this->articleContent);
        $this->category = $this->article->getCategory();

        if (!$this->article->canRead()) {
            throw new PermissionDeniedException();
        }

        // update interface language
        if (
            WCF::getUser()->isGuest()
            && $this->article->isMultilingual
            && $this->articleContent->languageID !== null
            && $this->articleContent->languageID != WCF::getLanguage()->languageID
        ) {
            WCF::setLanguage($this->articleContent->languageID);
        }

        $this->canonicalURL = $this->articleContent->getLink();
    }

    #[\Override]
    public function readData(): void
    {
        parent::readData();

        MessageEmbeddedObjectManager::getInstance()->setActiveMessage(
            'com.woltlab.wcf.article.content',
            $this->articleContent->getObjectID()
        );

        $this->updateViewCounter();
        $this->markAsRead();
        $this->loadTags();
        $this->loadRelatedArticles();
        $this->setLocation();
        $this->loadNextArticle();
        $this->loadPreviousArticle();
        $this->setMetaTags();
        $this->setOpenGraphImageTags();
    }

    protected function updateViewCounter(): void
    {
        if (!$this->article->isPublished()) {
            return;
        }

        $articleEditor = new ArticleEditor($this->article);
        $articleEditor->updateCounters([
            'views' => 1,
        ]);
    }

    protected function markAsRead(): void
    {
        if (!$this->article->isNew()) {
            return;
        }

        (new MarkArticleAsRead($this->article))();
    }

    protected function loadTags(): void
    {
        if (\MODULE_TAGGING === 0 || !WCF::getSession()->hasPermission('user.tag.canViewTag')) {
            return;
        }

        $this->tags = TagEngine::getInstance()->getObjectTags(
            'com.woltlab.wcf.article',
            $this->articleContent->articleContentID,
            [$this->articleContent->languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()]
        );
    }

    protected function loadRelatedArticles(): void
    {
        if (\MODULE_TAGGING === 0 || \ARTICLE_RELATED_ARTICLES === 0 || $this->tags === []) {
            return;
        }

        $this->relatedArticleListView = new RelatedArticleListView($this->articleContent->articleContentID);
    }

    protected function setLocation(): void
    {
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
    }

    protected function loadNextArticle(): void
    {
        $articleList = new CategoryArticleList($this->article->categoryID);
        $articleList->getConditionBuilder()->add(
            'article.time ' . (\ARTICLE_SORT_ORDER == 'DESC' ? '>' : '<') . ' ?',
            [$this->article->time]
        );
        $articleList->sqlOrderBy = 'article.time ' . (\ARTICLE_SORT_ORDER == 'DESC' ? 'ASC' : 'DESC');
        $articleList->sqlLimit = 1;
        $articleList->readObjects();
        foreach ($articleList as $article) {
            $this->nextArticle = $article;
        }
    }

    protected function loadPreviousArticle(): void
    {
        $articleList = new CategoryArticleList($this->article->categoryID);
        $articleList->getConditionBuilder()->add(
            'article.time ' . (\ARTICLE_SORT_ORDER == 'DESC' ? '<' : '>') . ' ?',
            [$this->article->time]
        );
        $articleList->sqlOrderBy = 'article.time ' . \ARTICLE_SORT_ORDER;
        $articleList->sqlLimit = 1;
        $articleList->readObjects();
        foreach ($articleList as $article) {
            $this->previousArticle = $article;
        }
    }

    protected function setMetaTags(): void
    {
        MetaTagHandler::getInstance()->addTag(
            'og:title',
            'og:title',
            $this->articleContent->getTitle() . ' - ' . WCF::getLanguage()->get(\PAGE_TITLE),
            true
        );
        MetaTagHandler::getInstance()->addTag('og:url', 'og:url', $this->articleContent->getLink(), true);
        MetaTagHandler::getInstance()->addTag('og:type', 'og:type', 'article', true);
        MetaTagHandler::getInstance()->addTag(
            'og:description',
            'og:description',
            ($this->articleContent->teaser ?: StringUtil::decodeHTML(StringUtil::stripHTML($this->articleContent->getFormattedTeaser()))),
            true
        );

        if ($this->articleContent->metaDescription) {
            MetaTagHandler::getInstance()->addTag('description', 'description', $this->articleContent->metaDescription);
        }

        if ($this->tags !== []) {
            $keywords = '';
            foreach ($this->tags as $tag) {
                if ($keywords !== '') {
                    $keywords .= ', ';
                }
                $keywords .= $tag->name;
            }
            MetaTagHandler::getInstance()->addTag('keywords', 'keywords', $keywords);
        }
    }

    protected function setOpenGraphImageTags(): void
    {
        if ($this->articleContent->getTeaserImage() && $this->articleContent->getTeaserImage()->width >= 200 && $this->articleContent->getTeaserImage()->height >= 200) {
            MetaTagHandler::getInstance()->addTag(
                'og:image',
                'og:image',
                $this->articleContent->getTeaserImage()->getThumbnailLink('large'),
                true
            );
            MetaTagHandler::getInstance()->addTag(
                'og:image:width',
                'og:image:width',
                (string)$this->articleContent->getTeaserImage()->getThumbnailWidth('large'),
                true
            );
            MetaTagHandler::getInstance()->addTag(
                'og:image:height',
                'og:image:height',
                (string)$this->articleContent->getTeaserImage()->getThumbnailHeight('large'),
                true
            );
        } elseif ($this->articleContent->getImage()) {
            MetaTagHandler::getInstance()->addTag(
                'og:image',
                'og:image',
                $this->articleContent->getImage()->getThumbnailLink('large'),
                true
            );
            MetaTagHandler::getInstance()->addTag(
                'og:image:width',
                'og:image:width',
                (string)$this->articleContent->getImage()->getThumbnailWidth('large'),
                true
            );
            MetaTagHandler::getInstance()->addTag(
                'og:image:height',
                'og:image:height',
                (string)$this->articleContent->getImage()->getThumbnailHeight('large'),
                true
            );
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'articleContentID' => $this->articleContent->getObjectID(),
            'articleContent' => $this->articleContent,
            'article' => $this->article,
            'category' => $this->category,
            'relatedArticleListView' => $this->relatedArticleListView ?? null,
            'tags' => $this->tags,
            'previousArticle' => $this->previousArticle,
            'nextArticle' => $this->nextArticle,
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentInteractionButton(
                new ArticleInteractions(),
                $this->article,
                LinkHandler::getInstance()->getControllerLink(ArticleListPage::class),
                WCF::getLanguage()->getDynamicVariable('wcf.acp.article.edit'),
                "core/articles/contents/{$this->articleContent->getObjectID()}/content-header-title"
            ),
        ]);
    }
}
