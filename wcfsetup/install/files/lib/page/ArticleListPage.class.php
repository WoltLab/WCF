<?php

namespace wcf\page;

use wcf\data\article\AccessibleArticleList;
use wcf\data\article\category\ArticleCategory;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\label\LabelHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows a list of cms articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class ArticleListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $itemsPerPage = ARTICLES_PER_PAGE;

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_ARTICLE'];

    /**
     * @inheritDoc
     */
    public $sortField = 'time';

    /**
     * @inheritDoc
     */
    public $sortOrder = ARTICLE_SORT_ORDER;

    /**
     * @inheritDoc
     */
    public $objectListClassName = AccessibleArticleList::class;

    /**
     * label filter
     * @var int[]
     */
    public $labelIDs = [];

    /**
     * list of available label groups
     * @var ViewableLabelGroup[]
     */
    public $labelGroups = [];

    /**
     * controller name
     * @var string
     */
    public $controllerName = 'ArticleList';

    /**
     * url parameters
     * @var array
     */
    public $controllerParameters = ['application' => 'wcf'];

    /**
     * @var User
     * @since   5.2
     */
    public $user;

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'time';

    /**
     * @inheritDoc
     */
    public $defaultSortOrder = 'DESC';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['title', 'time'];

    /**
     * display 'Add Article' dialog on load
     * @var int
     */
    public $showArticleAddDialog = 0;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['showArticleAddDialog'])) {
            $this->showArticleAddDialog = 1;
        }

        // read available label groups
        $this->labelGroups = $this->getLabelGroups();
        if (!empty($this->labelGroups) && isset($_REQUEST['labelIDs']) && \is_array($_REQUEST['labelIDs'])) {
            $this->labelIDs = $_REQUEST['labelIDs'];

            foreach ($this->labelIDs as $groupID => $labelID) {
                $isValid = false;

                // ignore zero-values
                if (!\is_array($labelID) && $labelID) {
                    if (isset($this->labelGroups[$groupID]) && ($labelID == -1 || $this->labelGroups[$groupID]->isValid($labelID))) {
                        $isValid = true;
                    }
                }

                if (!$isValid) {
                    unset($this->labelIDs[$groupID]);
                }
            }
        }

        if (!empty($_GET['userID'])) {
            $this->user = new User(\intval($_GET['userID']));
            if (!$this->user->userID) {
                throw new IllegalLinkException();
            }

            $this->controllerParameters['userID'] = $this->user->userID;
        }

        if (!empty($_POST)) {
            $labelParameters = '';
            if (!empty($this->labelIDs)) {
                foreach ($this->labelIDs as $groupID => $labelID) {
                    $labelParameters .= 'labelIDs[' . $groupID . ']=' . $labelID . '&';
                }
            }

            HeaderUtil::redirect(LinkHandler::getInstance()->getLink(
                $this->controllerName,
                $this->controllerParameters,
                \rtrim($labelParameters, '&')
            ));

            exit;
        }

        $this->canonicalURL = LinkHandler::getInstance()->getLink(
            'ArticleList',
            $this->controllerParameters,
            ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : '')
        );
    }

    /**
     * Returns the label groups shown on this page.
     *
     * @return      ViewableLabelGroup[]
     * @since       5.4
     */
    protected function getLabelGroups(): array
    {
        return ArticleCategory::getAccessibleLabelGroups('canViewLabel');
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->applyFilters();

        if ($this->sortField === 'title') {
            if (!empty($this->objectList->sqlSelects)) {
                $this->objectList->sqlSelects .= ',';
            }
            $this->objectList->sqlSelects .= "(
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

    protected function applyFilters()
    {
        if ($this->user) {
            $this->objectList->getConditionBuilder()->add("article.userID = ?", [$this->user->userID]);
        }

        // filter by label
        if (!empty($this->labelIDs)) {
            $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeByName(
                'com.woltlab.wcf.label.object',
                'com.woltlab.wcf.article'
            )->objectTypeID;

            foreach ($this->labelIDs as $groupID => $labelID) {
                if ($labelID == -1) {
                    $groupLabelIDs = LabelHandler::getInstance()->getLabelGroup($groupID)->getLabelIDs();

                    if (!empty($groupLabelIDs)) {
                        $this->objectList->getConditionBuilder()->add(
                            'article.articleID NOT IN (
                                SELECT  objectID
                                FROM    wcf1_label_object
                                WHERE   objectTypeID = ?
                                    AND labelID IN (?)
                            )',
                            [
                                $objectTypeID,
                                $groupLabelIDs,
                            ]
                        );
                    }
                } else {
                    $this->objectList->getConditionBuilder()->add(
                        'article.articleID IN (
                            SELECT  objectID
                            FROM    wcf1_label_object
                            WHERE   objectTypeID = ?
                                AND labelID = ?
                        )',
                        [
                            $objectTypeID,
                            $labelID,
                        ]
                    );
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'labelGroups' => $this->labelGroups,
            'labelIDs' => $this->labelIDs,
            'controllerName' => $this->controllerName,
            'controllerObject' => null,
            'user' => $this->user,
            'categoryID' => 0,
            'showArticleAddDialog' => $this->showArticleAddDialog,
            'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
        ]);
    }
}
