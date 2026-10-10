<?php

namespace wcf\system\box;

use wcf\data\article\AccessibleArticleList;
use wcf\data\article\Article;
use wcf\data\article\category\ArticleCategory;
use wcf\page\CategoryArticleListPage;
use wcf\page\ArticleListPage;
use wcf\system\listView\user\ArticleListView;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Box controller for a list of articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractListViewBoxController<ArticleListView>
 */
class ArticleListBoxController extends AbstractListViewBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = [
        'sidebarLeft',
        'sidebarRight',
        'contentTop',
        'contentBottom',
        'top',
        'bottom',
    ];

    /**
     * @inheritDoc
     */
    protected $sortFieldLanguageItemPrefix = 'wcf.article.sortField';

    /**
     * @inheritDoc
     */
    public $defaultLimit = 3;

    /**
     * @inheritDoc
     */
    protected $conditionDefinition = 'com.woltlab.wcf.box.articleList.condition';

    /**
     * @inheritDoc
     */
    public $validSortFields = [
        'time',
        'views',
    ];

    public function __construct()
    {
        if ($this->validSortFields !== [] && \MODULE_LIKE !== 0) {
            $this->validSortFields[] = 'cumulativeLikes';
        }

        parent::__construct();
    }

    #[\Override]
    protected function getObjectList(): AccessibleArticleList
    {
        $objectList = $this->getListView()->getObjectList();
        $objectList->getConditionBuilder()->add('article.isDeleted = ?', [0]);
        $objectList->getConditionBuilder()->add('article.publicationStatus = ?', [Article::PUBLISHED]);

        switch ($this->sortField) {
            case 'views':
                $objectList->getConditionBuilder()->add('article.views > ?', [0]);
                break;
        }

        return $objectList;
    }

    #[\Override]
    protected function createListView(): ArticleListView
    {
        return new ArticleListView();
    }

    #[\Override]
    protected function getTemplate()
    {
        return match ($this->box->position) {
            'top', 'bottom', 'contentTop', 'contentBottom' => parent::getTemplate(),
            default => WCF::getTPL()->render('wcf', 'boxArticleList', [
                'boxArticleList' => $this->getListView()->getItems(),
                'boxSortField' => $this->sortField,
                'boxPosition' => $this->box->position,
            ])
        };
    }

    #[\Override]
    public function hasLink(): bool
    {
        return \MODULE_ARTICLE !== 0;
    }

    #[\Override]
    public function getLink(): string
    {
        $parameters = [];
        if (($this->sortField ?? '') !== '' && ($this->sortOrder ?? '') !== '') {
            $parameters['sortField'] = $this->sortField;
            $parameters['sortOrder'] = $this->sortOrder;
        }

        $category = $this->getLinkedCategory();
        if ($category !== null) {
            $parameters['object'] = $category->getDecoratedObject();

            return LinkHandler::getInstance()->getControllerLink(CategoryArticleListPage::class, $parameters);
        }

        return LinkHandler::getInstance()->getControllerLink(ArticleListPage::class, $parameters);
    }

    /**
     * Returns the category the box is restricted to by its category condition, or `null`
     * if the box is not restricted to exactly one accessible category.
     */
    private function getLinkedCategory(): ?ArticleCategory
    {
        $categoryIDs = $this->getConditionData('com.woltlab.wcf.articleCategory')['articleCategoryIDs'] ?? [];
        if (\count($categoryIDs) !== 1) {
            return null;
        }

        $category = ArticleCategory::getCategory((int)\reset($categoryIDs));
        if ($category === null || !$category->isAccessible()) {
            return null;
        }

        return $category;
    }
}
