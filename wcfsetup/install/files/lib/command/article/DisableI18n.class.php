<?php

namespace wcf\command\article;

use wcf\command\article\content\DeleteArticleContent;
use wcf\data\article\Article;
use wcf\data\article\ArticleAction;
use wcf\data\article\content\ArticleContentBuilder;
use wcf\data\language\Language;
use wcf\system\version\VersionTracker;

/**
 * Converts a multilingual article to a monolingual.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class DisableI18n
{
    public function __construct(
        private readonly Article $article,
        private readonly Language $language
    ) {}

    public function __invoke(): void
    {
        $removeContents = [];

        foreach ($this->article->getArticleContents() as $articleContent) {
            if ($articleContent->languageID == $this->language->languageID) {
                ArticleContentBuilder::forUpdate($articleContent)
                    ->setLanguageID(null)
                    ->update();
            } else {
                $removeContents[] = $articleContent;
            }
        }

        if ($removeContents !== []) {
            foreach ($removeContents as $articleContent) {
                (new DeleteArticleContent($articleContent))();
            }
        }

        $action = new ArticleAction([$this->article], 'update', [
            'data' => [
                'isMultilingual' => 0,
            ],
        ]);
        $action->executeAction();

        VersionTracker::getInstance()->reset(
            'com.woltlab.wcf.article',
            $this->article->articleID
        );
    }
}
