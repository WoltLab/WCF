<?php

namespace wcf\page;

use wcf\data\article\category\ArticleCategory;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\listView\user\ArticleListView;
use wcf\system\listView\user\CategoryArticleListView;
use wcf\system\MetaTagHandler;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of cms articles in a certain category.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class CategoryArticleListPage extends ArticleListPage
{
    /**
     * Category the listed articles belong to.
     */
    public ?ArticleCategory $category = null;

    /**
     * Id of the category the listed articles belong to.
     */
    public int $categoryID = 0;

    #[\Override]
    public function readParameters()
    {
        if (isset($_REQUEST['id'])) {
            $this->categoryID = \intval($_REQUEST['id']);
        }
        $this->category = ArticleCategory::getCategory($this->categoryID);
        if ($this->category === null) {
            throw new IllegalLinkException();
        }

        parent::readParameters();
    }

    #[\Override]
    protected function createListView(): ArticleListView
    {
        return new CategoryArticleListView($this->category->categoryID);
    }

    #[\Override]
    protected function initListView(): void
    {
        parent::initListView();

        $this->listView->setBaseUrl(LinkHandler::getInstance()->getControllerLink(static::class, [
            'object' => $this->category,
        ]));
    }

    #[\Override]
    public function checkPermissions()
    {
        parent::checkPermissions();

        if (!$this->category->isAccessible()) {
            throw new PermissionDeniedException();
        }
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        // set location
        foreach ($this->category->getParentCategories() as $parentCategory) {
            PageLocationManager::getInstance()->addParentLocation(
                'com.woltlab.wcf.CategoryArticleList',
                $parentCategory->categoryID,
                $parentCategory
            );
        }

        // Add meta tags.
        MetaTagHandler::getInstance()->addTag(
            'og:title',
            'og:title',
            $this->category->getTitle() . ' - ' . WCF::getLanguage()->get(PAGE_TITLE),
            true
        );
        MetaTagHandler::getInstance()->addTag(
            'og:url',
            'og:url',
            $this->category->getLink(),
            true
        );
        if ($this->category->getDescription()) {
            $description = $this->category->getDescription();
            if ($this->category->descriptionUseHtml) {
                $description = StringUtil::decodeHTML(StringUtil::stripHTML($description));
            }
            MetaTagHandler::getInstance()->addTag(
                'og:description',
                'og:description',
                $description,
                true
            );
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'categoryID' => $this->categoryID,
            'category' => $this->category,
        ]);
    }
}
