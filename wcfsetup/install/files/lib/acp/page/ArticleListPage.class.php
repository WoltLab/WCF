<?php

namespace wcf\acp\page;

use wcf\data\article\AccessibleArticleList;
use wcf\data\article\ArticleList;
use wcf\data\category\CategoryNodeTree;
use wcf\data\user\User;
use wcf\page\SortablePage;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of cms articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @property    ArticleList $objectList
 */
class ArticleListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.article.list';

    /**
     * @inheritDoc
     */
    public $objectListClassName = AccessibleArticleList::class;

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_ARTICLE'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = [
        'admin.content.article.canManageArticle',
        'admin.content.article.canManageOwnArticles',
        'admin.content.article.canContributeArticle',
    ];

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
    public $validSortFields = ['articleID', 'title', 'time', 'views'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 50;

    /**
     * category id
     * @var int
     */
    public $categoryID = 0;

    /**
     * name
     * @var string
     */
    public $username = '';

    /**
     * title
     * @var string
     */
    public $title = '';

    /**
     * content
     * @var string
     */
    public $content = '';

    /**
     * display 'Add Article' dialog on load
     * @var int
     */
    public $showArticleAddDialog = 0;

    /**
     * publication status filter
     * @var int
     */
    public $publicationStatus = -1;

    /**
     * @var int
     */
    public $isDeleted = -1;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['categoryID'])) {
            $this->categoryID = \intval($_REQUEST['categoryID']);
        }
        if (!empty($_REQUEST['username'])) {
            $this->username = StringUtil::trim($_REQUEST['username']);
        }
        if (!empty($_REQUEST['title'])) {
            $this->title = StringUtil::trim($_REQUEST['title']);
        }
        if (!empty($_REQUEST['content'])) {
            $this->content = StringUtil::trim($_REQUEST['content']);
        }
        if (!empty($_REQUEST['showArticleAddDialog'])) {
            $this->showArticleAddDialog = 1;
        }
        if (isset($_REQUEST['publicationStatus'])) {
            $this->publicationStatus = \intval($_REQUEST['publicationStatus']);
        }
        if (!empty($_REQUEST['isDeleted'])) {
            $this->isDeleted = \intval($_REQUEST['isDeleted']);
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        if ($this->categoryID) {
            $this->objectList->getConditionBuilder()->add('article.categoryID = ?', [$this->categoryID]);
        }
        if (!empty($this->username)) {
            $user = User::getUserByUsername($this->username);
            if ($user->userID) {
                $this->objectList->getConditionBuilder()->add('article.userID = ?', [$user->userID]);
            } else {
                $this->objectList->getConditionBuilder()->add('1=0');
            }
        }
        if (!empty($this->title)) {
            $this->objectList->getConditionBuilder()->add(
                'article.articleID IN (
                    SELECT  articleID
                    FROM    wcf1_article_content
                    WHERE   title LIKE ?
                )',
                ['%' . $this->title . '%']
            );
        }
        if (!empty($this->content)) {
            $this->objectList->getConditionBuilder()->add(
                'article.articleID IN (
                    SELECT  articleID
                    FROM    wcf1_article_content
                    WHERE   content LIKE ?
                )',
                ['%' . $this->content . '%']
            );
        }

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

        if ($this->publicationStatus != -1) {
            $this->objectList->getConditionBuilder()->add('article.publicationStatus = ?', [$this->publicationStatus]);
        }

        if ($this->isDeleted !== -1) {
            $this->objectList->getConditionBuilder()->add('article.isDeleted = ?', [$this->isDeleted]);
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
            'username' => $this->username,
            'title' => $this->title,
            'content' => $this->content,
            'showArticleAddDialog' => $this->showArticleAddDialog,
            'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
            'categoryNodeList' => (new CategoryNodeTree('com.woltlab.wcf.article.category'))->getIterator(),
            'publicationStatus' => $this->publicationStatus,
            'hasMarkedItems' => ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.article')),
            'isDeleted' => $this->isDeleted,
        ]);
    }
}
