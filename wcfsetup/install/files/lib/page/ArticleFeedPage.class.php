<?php

namespace wcf\page;

use wcf\data\article\category\ArticleCategory;
use wcf\data\article\FeedArticleList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Shows a list of cms articles in a certain category in feed.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 * @deprecated 6.1 use `ArticleRssFeedPage` instead
 */
class ArticleFeedPage extends AbstractFeedPage
{
    /**
     * category the listed articles belong to
     * @var ArticleCategory
     */
    public $category;

    /**
     * id of the category the listed articles belong to
     * @var int
     */
    public $categoryID = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->categoryID = \intval($_REQUEST['id']);
            $this->category = ArticleCategory::getCategory($this->categoryID);
            if ($this->category === null) {
                throw new IllegalLinkException();
            }
            if (!$this->category->isAccessible()) {
                throw new PermissionDeniedException();
            }
        }

        $this->redirectToNewPage(ArticleRssFeedPage::class);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // read the articles
        $this->items = new FeedArticleList($this->categoryID);
        $this->items->sqlOrderBy = 'article.time ' . ARTICLE_SORT_ORDER;
        $this->items->sqlLimit = 20;
        $this->items->readObjects();

        // set title
        if ($this->category !== null) {
            $this->title = $this->category->getTitle();
        } else {
            $this->title = WCF::getLanguage()->get('wcf.article.articles');
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'supportsEnclosure' => true,
        ]);
    }
}
