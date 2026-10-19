<?php

namespace wcf\data\article\content;

use wcf\data\article\Article;
use wcf\data\DatabaseObjectCollection;
use wcf\data\media\Media;
use wcf\data\media\MediaList;
use wcf\data\TCollectionAttachments;
use wcf\data\TCollectionEmbeddedObjects;
use wcf\system\cache\runtime\ArticleRuntimeCache;

/**
 * Represents a collection of article contents.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<ArticleContent>
 */
class ArticleContentCollection extends DatabaseObjectCollection
{
    use TCollectionEmbeddedObjects;
    use TCollectionAttachments;

    /**
     * @var array<int, Media>
     */
    private array $images;

    private bool $articlesCached = false;

    public function getImage(int $imageID): ?Media
    {
        $this->loadImages();

        return $this->images[$imageID] ?? null;
    }

    private function loadImages(): void
    {
        if (isset($this->images)) {
            return;
        }

        $this->images = [];
        $imageIDs = $this->getImageIDs();
        if ($imageIDs === []) {
            return;
        }

        $mediaList = new MediaList();
        $mediaList->setObjectIDs($imageIDs);
        $mediaList->readObjects();
        $this->images = $mediaList->getObjects();
    }

    /**
     * @return list<int>
     */
    private function getImageIDs(): array
    {
        $imageIDs = [];

        foreach ($this->getObjects() as $object) {
            if ($object->imageID !== null) {
                $imageIDs[] = $object->imageID;
            }
            if ($object->teaserImageID !== null) {
                $imageIDs[] = $object->teaserImageID;
            }
        }

        return \array_unique($imageIDs);
    }

    public function getArticle(ArticleContent $content): ?Article
    {
        $this->cacheArticles();

        return ArticleRuntimeCache::getInstance()->getObject($content->articleID);
    }

    private function cacheArticles(): void
    {
        if ($this->articlesCached) {
            return;
        }

        $this->articlesCached = true;

        $articleIDs = \array_unique(\array_map(
            fn($object) => $object->articleID,
            $this->getObjects()
        ));

        ArticleRuntimeCache::getInstance()->cacheObjectIDs($articleIDs);
    }

    #[\Override]
    protected function getAttachmentObjectType(): string
    {
        return 'com.woltlab.wcf.article.content';
    }
}
