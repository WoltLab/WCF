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
 * @deprecated 6.1
 */
class FeedArticle extends ViewableArticle implements IFeedEntryWithEnclosure
{
    use TUserContent;

    /**
     * @var FeedEnclosure
     */
    protected $enclosure;

    #[\Override]
    public function getLink(): string
    {
        return $this->getDecoratedObject()->getLink();
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->getDecoratedObject()->getTitle();
    }

    #[\Override]
    public function getFormattedMessage()
    {
        return $this->getDecoratedObject()->getFormattedContent();
    }

    #[\Override]
    public function getMessage()
    {
        return $this->getDecoratedObject()->getTeaser();
    }

    #[\Override]
    public function getExcerpt(int $maxLength = 255)
    {
        return StringUtil::truncateHTML($this->getDecoratedObject()->getFormattedTeaser(), $maxLength);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getMessage();
    }

    #[\Override]
    public function getComments()
    {
        return $this->getArticleContent()->comments;
    }

    #[\Override]
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

    #[\Override]
    public function isVisible()
    {
        return $this->canRead();
    }

    #[\Override]
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
