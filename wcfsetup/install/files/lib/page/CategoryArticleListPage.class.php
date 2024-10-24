<?php

namespace wcf\page;

use wcf\data\article\category\ArticleCategory;
use wcf\data\article\CategoryArticleList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\MetaTagHandler;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of cms articles in a certain category.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class CategoryArticleListPage extends ArticleListPage
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
    public $controllerName = 'CategoryArticleList';

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        if (isset($_REQUEST['id'])) {
            $this->categoryID = \intval($_REQUEST['id']);
        }
        $this->category = ArticleCategory::getCategory($this->categoryID);
        if ($this->category === null) {
            throw new IllegalLinkException();
        }
        $this->controllerParameters['object'] = $this->category;
        parent::readParameters();

        $this->canonicalURL = LinkHandler::getInstance()->getLink('CategoryArticleList', [
            'object' => $this->category,
        ], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));

        if ($this->category->sortField) {
            $this->sortField = $this->category->sortField;
            $this->sortOrder = $this->category->sortOrder;
        }
    }

    /**
     * @inheritDoc
     */
    protected function getLabelGroups(): array
    {
        return $this->category->getLabelGroups('canViewLabel');
    }

    /**
     * @inheritDoc
     */
    public function checkPermissions()
    {
        parent::checkPermissions();

        if (!$this->category->isAccessible()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        $this->objectList = new CategoryArticleList($this->categoryID, true);
        $this->applyFilters();

        if ($this->sortField === 'title') {
            $this->objectList->sqlSelects = "(
                SELECT  title
                FROM    wcf1_article_content
                WHERE   articleID = article.articleID
                    AND (
                            languageID IS NULL
                         OR languageID = " . WCF::getLanguage()->languageID . "
                         )
                LIMIT   1
            ) AS title";
        }
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'categoryID' => $this->categoryID,
            'category' => $this->category,
            'controllerObject' => $this->category,
        ]);
    }
}
