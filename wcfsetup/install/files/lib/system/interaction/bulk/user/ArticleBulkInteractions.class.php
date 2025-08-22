<?php

namespace wcf\system\interaction\bulk\user;

use wcf\data\article\AccessibleArticleList;
use wcf\data\article\Article;
use wcf\data\article\ViewableArticle;
use wcf\event\interaction\bulk\user\ArticleBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\interaction\bulk\BulkRestoreInteraction;
use wcf\system\interaction\bulk\BulkRpcInteraction;
use wcf\system\interaction\bulk\BulkSoftDeleteInteraction;
use wcf\system\interaction\InteractionConfirmationType;
use wcf\system\WCF;

/**
 * Bulk interaction provider for articles.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ArticleBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.content.article.canManageArticle')) {
            return;
        }

        $this->addInteractions([
            new BulkSoftDeleteInteraction('core/articles/%s/soft-delete', function (ViewableArticle $article): bool {
                if (!$article->canDelete()) {
                    return false;
                }

                return $article->isDeleted !== 1;
            }),
            new BulkRestoreInteraction('core/articles/%s/restore', function (ViewableArticle $article): bool {
                if (!$article->canDelete()) {
                    return false;
                }

                return $article->isDeleted === 1;
            }),
            new BulkDeleteInteraction('core/articles/%s', function (ViewableArticle $article): bool {
                if (!$article->canDelete()) {
                    return false;
                }

                return $article->isDeleted === 1;
            }),
            new BulkRpcInteraction(
                'publish',
                'core/articles/%s/publish',
                'wcf.article.button.publish',
                InteractionConfirmationType::None,
                '',
                function (ViewableArticle $article): bool {
                    if (!$article->canPublish()) {
                        return false;
                    }

                    return $article->publicationStatus !== Article::PUBLISHED;
                }
            ),
            new BulkRpcInteraction(
                'unpublish',
                'core/articles/%s/unpublish',
                'wcf.article.button.unpublish',
                InteractionConfirmationType::None,
                '',
                function (ViewableArticle $article): bool {
                    if (!$article->canPublish()) {
                        return false;
                    }

                    return $article->publicationStatus === Article::PUBLISHED;
                }
            ),
        ]);

        EventHandler::getInstance()->fire(
            new ArticleBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return AccessibleArticleList::class;
    }
}
