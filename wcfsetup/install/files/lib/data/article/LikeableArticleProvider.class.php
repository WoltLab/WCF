<?php

namespace wcf\data\article;

use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\object\ILikeObject;
use wcf\data\object\type\AbstractObjectTypeProvider;
use wcf\system\cache\runtime\ViewableArticleRuntimeCache;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Like object type provider for cms articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractObjectTypeProvider<LikeableArticle>
 * @implements ILikeObjectTypeProvider<LikeableArticle>
 */
class LikeableArticleProvider extends AbstractObjectTypeProvider implements
    ILikeObjectTypeProvider,
    IViewableLikeProvider
{
    /**
     * @inheritDoc
     */
    public $className = Article::class;

    /**
     * @inheritDoc
     */
    public $listClassName = ArticleList::class;

    /**
     * @inheritDoc
     */
    public $decoratorClassName = LikeableArticle::class;

    #[\Override]
    public function checkPermissions(ILikeObject $object)
    {
        \assert($object instanceof LikeableArticle);

        return $object->articleID && $object->canRead();
    }

    #[\Override]
    public function prepare(array $likes)
    {
        $objectIDs = [];
        foreach ($likes as $like) {
            $objectIDs[] = $like->objectID;
        }

        ViewableArticleRuntimeCache::getInstance()->cacheObjectIDs($objectIDs);

        foreach ($likes as $like) {
            $article = ViewableArticleRuntimeCache::getInstance()->getObject($like->objectID);
            if ($article === null || !$article->canRead()) {
                continue;
            }

            $like->setIsAccessible();

            $like->setTitle(WCF::getLanguage()->getDynamicVariable(
                'wcf.article.recentActivity.likedArticle',
                [
                    'article' => $article,
                    'reactionType' => $like->getReactionType(),
                    'author' => $like->getUserProfile(),
                ]
            ));
            $like->setLink($article->getLink());
            $like->setDescription(\strip_tags($article->getFormattedTeaser()));
        }
    }
}
