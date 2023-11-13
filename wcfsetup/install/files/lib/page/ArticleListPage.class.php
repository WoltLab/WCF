<?php

namespace wcf\page;

use wcf\data\article\AccessibleArticleList;
use wcf\data\article\category\ArticleCategory;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\label\LabelPickerGroup;
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
     * @since 6.1
     */
    public LabelPickerGroup $labelPickerGroup;

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

        $this->labelPickerGroup = LabelPickerGroup::fromViewableLabelGroups($this->getLabelGroups(), true);
        $labelIDs = $_REQUEST['labelIDs'] ?? [];
        if (\is_array($labelIDs)) {
            $this->labelPickerGroup->setSelectedLabels($labelIDs);
        }

        if (!empty($_GET['userID'])) {
            $this->user = new User(\intval($_GET['userID']));
            if (!$this->user->userID) {
                throw new IllegalLinkException();
            }

            $this->controllerParameters['userID'] = $this->user->userID;
        }

        if ($_POST !== []) {
            HeaderUtil::redirect(LinkHandler::getInstance()->getLink(
                $this->controllerName,
                $this->controllerParameters,
                $this->labelPickerGroup->toUrlQueryString(),
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
                FROM    wcf" . WCF_N . "_article_content
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

        if (\count($this->labelPickerGroup)) {
            $this->labelPickerGroup->applyFilter(
                'com.woltlab.wcf.article',
                'article.articleID',
                $this->objectList->getConditionBuilder(),
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
            'labelPickerGroup' => $this->labelPickerGroup,
            'controllerName' => $this->controllerName,
            'controllerObject' => null,
            'user' => $this->user,
            'categoryID' => 0,
            'showArticleAddDialog' => $this->showArticleAddDialog,
            'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),

            // @deprecated 6.1 The variable is kept for backwards compatibility only.
            'labelGroups' => [],
            // @deprecated 6.1 The variable is kept for backwards compatibility only.
            'labelIDs' => [],
        ]);
    }
}
