<?php

namespace wcf\page;

use wcf\system\language\LanguageFactory;
use wcf\system\listView\user\ArticleListView;
use wcf\system\WCF;

/**
 * Shows a list of cms articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractListViewPage<ArticleListView>
 */
class ArticleListPage extends AbstractListViewPage
{
    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_ARTICLE'];

    /**
     * Displays the 'Add Article' dialog on load.
     */
    public bool $showArticleAddDialog = false;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['showArticleAddDialog'])) {
            $this->showArticleAddDialog = true;
        }
    }

    #[\Override]
    protected function createListView(): ArticleListView
    {
        return new ArticleListView();
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'canManageArticles' => WCF::getSession()->getPermission('admin.content.article.canManageArticle')
                || WCF::getSession()->getPermission('admin.content.article.canManageOwnArticles')
                || WCF::getSession()->getPermission('admin.content.article.canContributeArticle'),
            'categoryID' => 0,
            'showArticleAddDialog' => $this->showArticleAddDialog,
            'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
        ]);
    }
}
