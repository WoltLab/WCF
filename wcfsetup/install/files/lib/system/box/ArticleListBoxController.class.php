<?php

namespace wcf\system\box;

use wcf\system\listView\user\ArticleListView;
use wcf\system\WCF;

/**
 * Box controller for a list of articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
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
        'footerBoxes',
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
        if (!empty($this->validSortFields) && MODULE_LIKE) {
            $this->validSortFields[] = 'cumulativeLikes';
        }

        parent::__construct();
    }

    #[\Override]
    protected function getObjectList()
    {
        $objectList = $this->getListView()->getObjectList();

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
        if (!isset($this->listView)) {
            $this->listView = new ArticleListView();
        }

        return $this->listView;
    }

    #[\Override]
    public function getContainerCssClassName(): string
    {
        return 'entryCardList__container';
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
}
