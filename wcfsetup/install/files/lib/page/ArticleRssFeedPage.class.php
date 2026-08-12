<?php

namespace wcf\page;

use wcf\data\article\AccessibleArticleList;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\CategoryArticleList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\rssFeed\RssFeed;
use wcf\system\rssFeed\RssFeedItem;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Outputs a list of cms articles of a certain category as a rss feed.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
class ArticleRssFeedPage extends AbstractRssFeedPage
{
    public ArticleCategory $category;
    public int $categoryID = 0;
    public AccessibleArticleList $articles;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if ($this->objectIDs !== []) {
            $this->categoryID = \reset($this->objectIDs);
            $category = ArticleCategory::getCategory($this->categoryID);
            if ($category === null) {
                throw new IllegalLinkException();
            }
            if (!$category->isAccessible()) {
                throw new PermissionDeniedException();
            }
            $this->category = $category;
        }
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        if ($this->categoryID !== 0) {
            $this->articles = new CategoryArticleList($this->categoryID);
        } else {
            $this->articles = new AccessibleArticleList();
        }
        $this->articles->sqlOrderBy = 'article.time ' . \ARTICLE_SORT_ORDER;
        $this->articles->sqlLimit = 20;
        $this->articles->readObjects();
    }

    #[\Override]
    protected function getRssFeed(): RssFeed
    {
        $feed = new RssFeed();
        $channel = $this->getDefaultChannel();
        if (isset($this->category)) {
            $channel->title(\sprintf(
                '%s - %s',
                $this->category->getTitle(),
                WCF::getLanguage()->get(\PAGE_TITLE)
            ));
            $channel->description($this->category->getDecoratedObject()->getDescription());
        } else {
            $channel->title(\sprintf(
                '%s - %s',
                WCF::getLanguage()->get('wcf.article.articles'),
                WCF::getLanguage()->get(\PAGE_TITLE)
            ));
        }

        if ($this->articles->valid()) {
            $channel->lastBuildDateFromTimestamp($this->articles->current()->getTime());
        }
        $feed->channel($channel);

        foreach ($this->articles as $article) {
            $content = $article->getArticleContent();
            if ($content === null) {
                continue;
            }

            $link = $content->getLink();

            $item = new RssFeedItem();
            $item
                ->title($content->getTitle())
                ->link($link)
                ->description(StringUtil::truncateHTML($content->getFormattedTeaser(), 255))
                ->pubDateFromTimestamp($article->time)
                ->creator($article->username)
                ->guid($link)
                ->contentEncoded($content->getSimplifiedFormattedContent())
                ->slashComments($content->comments);

            if ($article->getImage() !== null) {
                $item->enclosure(
                    $article->getImage()->getThumbnailLink('small'),
                    $article->getImage()->smallThumbnailSize,
                    $article->getImage()->smallThumbnailType
                );
            }

            $category = $article->getCategory();
            if ($category !== null) {
                $item->category($category->getTitle());
                foreach ($category->getParentCategories() as $category) {
                    $item->category($category->getTitle());
                }
            }

            $channel->item($item);
        }

        return $feed;
    }
}
