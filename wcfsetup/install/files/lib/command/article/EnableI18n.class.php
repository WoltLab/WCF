<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleAction;
use wcf\data\article\content\ArticleContentAction;
use wcf\system\language\LanguageFactory;
use wcf\system\version\VersionTracker;

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

        $action = new ArticleAction([$this->article], 'update', [
            'content' => $data,
            'data' => [
                'isMultilingual' => 1,
            ],
        ]);
        $action->executeAction();

        $action = new ArticleContentAction([$articleContent], 'delete');
        $action->executeAction();

        VersionTracker::getInstance()->reset(
            'com.woltlab.wcf.article',
            $this->article->articleID
        );
    }
}
