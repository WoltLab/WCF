<?php

namespace wcf\system\interaction\user;

use wcf\data\article\Article;
use wcf\data\article\ViewableArticle;
use wcf\event\interaction\user\ArticleInteractionCollecting;
use wcf\form\ArticleEditForm;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\RestoreInteraction;
use wcf\system\interaction\RpcInteraction;
use wcf\system\interaction\SoftDeleteInteraction;

/**
 * Interaction provider for articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ArticleInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        $this->addInteractions([
            new SoftDeleteInteraction('core/articles/%s/soft-delete', function (ViewableArticle|Article $article): bool {
                if (!$article->canDelete()) {
                    return false;
                }

                return $article->isDeleted !== 1;
            }),
            new RestoreInteraction('core/articles/%s/restore', function (ViewableArticle|Article $article): bool {
                if (!$article->canDelete()) {
                    return false;
                }

                return $article->isDeleted === 1;
            }),
            new DeleteInteraction('core/articles/%s', function (ViewableArticle|Article $article): bool {
                if (!$article->canDelete()) {
                    return false;
                }

                return $article->isDeleted === 1;
            }),
            new RpcInteraction(
                'publish',
                'core/articles/%s/publish',
                'wcf.article.button.publish',
                isAvailableCallback: static function (ViewableArticle|Article $article): bool {
                    if (!$article->canPublish()) {
                        return false;
                    }

                    return $article->publicationStatus !== Article::PUBLISHED;
                }
            ),
            new RpcInteraction(
                'unpublish',
                'core/articles/%s/unpublish',
                'wcf.article.button.unpublish',
                isAvailableCallback: static function (ViewableArticle|Article $article): bool {
                    if (!$article->canPublish()) {
                        return false;
                    }

                    return $article->publicationStatus === Article::PUBLISHED;
                }
            ),
            new Divider(),
            new EditInteraction(ArticleEditForm::class, function (ViewableArticle|Article $article): bool {
                return $article->canEdit();
            })
        ]);

        EventHandler::getInstance()->fire(
            new ArticleInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return Article::class;
    }
}
