<?php

namespace wcf\system\box;

use wcf\data\article\AccessibleArticleList;
use wcf\system\WCF;

/**
 * Box controller for a list of articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @extends AbstractDatabaseObjectListBoxController<AccessibleArticleList>
 */
class ArticleListBoxController extends AbstractDatabaseObjectListBoxController
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
        'random',
    ];

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        if (!empty($this->validSortFields) && MODULE_LIKE) {
            $this->validSortFields[] = 'cumulativeLikes';
        }

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function getObjectList()
    {
        $objectList = new AccessibleArticleList();

        switch ($this->sortField) {
            case 'views':
                $objectList->getConditionBuilder()->add('article.views > ?', [0]);
                break;
        }

        if ($this->sortField === 'random') {
            $this->sortField = 'RAND()';
        }

        return $objectList;
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate()
    {
        return WCF::getTPL()->render('wcf', 'boxArticleList', [
            'boxArticleList' => $this->objectList,
            'boxSortField' => $this->sortField,
            'boxPosition' => $this->box->position,
        ]);
    }
}
