<?php

namespace wcf\data\article;

use wcf\data\IFeedEntryWithEnclosure;
use wcf\data\TUserContent;
use wcf\system\feed\enclosure\FeedEnclosure;
use wcf\util\StringUtil;

/**
 * Represents a viewable article for RSS feeds.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 * @deprecated 6.1
 */
class FeedArticle extends ViewableArticle implements IFeedEntryWithEnclosure
{
    use TUserContent;

    /**
     * @var FeedEnclosure
     */
    protected $enclosure;

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return $this->getDecoratedObject()->getLink();
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return $this->getDecoratedObject()->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getFormattedMessage()
    {
        return $this->getDecoratedObject()->getFormattedContent();
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getDecoratedObject()->getTeaser();
    }

    /**
     * @inheritDoc
     */
    public function getExcerpt($maxLength = 255)
    {
        return StringUtil::truncateHTML($this->getDecoratedObject()->getFormattedTeaser(), $maxLength);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getMessage();
    }

    /**
     * @inheritDoc
     */
    public function getComments()
    {
        return $this->getArticleContent()->comments;
    }

    /**
     * @inheritDoc
     */
    public function getCategories()
    {
        $categories = [];
        $category = $this->getDecoratedObject()->getCategory();
        if ($category !== null) {
            $categories[] = $category->getTitle();
            foreach ($category->getParentCategories() as $category) {
                $categories[] = $category->getTitle();
            }
        }

        return $categories;
    }

    /**
     * @inheritDoc
     */
    public function isVisible()
    {
        return $this->canRead();
    }

    /**
     * @inheritDoc
     */
    public function getEnclosure()
    {
        if ($this->enclosure === null) {
            if ($this->getImage() !== null) {
                $this->enclosure = new FeedEnclosure(
                    $this->getImage()->getThumbnailLink('small'),
                    $this->getImage()->smallThumbnailType,
                    $this->getImage()->smallThumbnailSize
                );
            }
        }

        return $this->enclosure;
    }
}
