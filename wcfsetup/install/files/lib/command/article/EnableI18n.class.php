<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleAction;
use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentAction;
use wcf\data\article\content\ArticleContentEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\article\discussion\IArticleDiscussionProvider;
use wcf\system\language\LanguageFactory;
use wcf\system\version\VersionTracker;
use wcf\system\WCF;

/**
 * Converts a monolingual article to a multilingual.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class EnableI18n
{
    public function __construct(
        private readonly Article $article,
    ) {}

    public function __invoke(): void
    {
        $articleContent = $this->article->getArticleContent();
        $data = [];
        foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
            $data[$language->languageID] = [
                'title' => $articleContent->title,
                'teaser' => $articleContent->teaser,
                'content' => $articleContent->content,
                'imageID' => $articleContent->imageID ?: null,
                'teaserImageID' => $articleContent->teaserImageID ?: null,
            ];
        }

        $discussionProvider = $this->article->getDiscussionProvider();

        $action = new ArticleAction([$this->article], 'update', [
            'content' => $data,
            'data' => [
                'isMultilingual' => 1,
            ],
            'migrateDiscussions' => true,
        ]);
        $action->executeAction();

        $this->migrateDiscussions($discussionProvider, $this->article, $articleContent);

        $action = new ArticleContentAction([$articleContent], 'delete');
        $action->executeAction();

        VersionTracker::getInstance()->reset(
            'com.woltlab.wcf.article',
            $this->article->articleID
        );
    }

    /**
     * Preserves the comments by associating the existing comments with the
     * content matching the site’s default language id.
     */
    private function migrateDiscussions(IArticleDiscussionProvider $discussionProvider, Article $article, ArticleContent $oldContent): void
    {
        $article = new Article($article->articleID);
        $newContent = $article->getArticleContents()[LanguageFactory::getInstance()->getDefaultLanguageID()] ?? null;
        if ($newContent === null) {
            // This shouldn’t be possible but throwing an exception here would
            // yield a malformed article that cannot be fixed except through
            // manual editing of the database.
            return;
        }

        $discussionProvider->migrateDiscussions($oldContent, $newContent);
    }
}
