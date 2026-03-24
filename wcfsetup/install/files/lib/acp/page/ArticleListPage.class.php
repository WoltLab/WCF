<?php

namespace wcf\acp\page;

use wcf\page\AbstractGridViewPage;
use wcf\system\gridView\admin\ArticleGridView;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Shows a list of cms articles.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @extends AbstractGridViewPage<ArticleGridView>
 */
final class ArticleListPage extends AbstractGridViewPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.article.list';

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
     * display 'Add Article' dialog on load
     * @var int
     */
    public $showArticleAddDialog = 0;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        if (!empty($_REQUEST['showArticleAddDialog'])) {
            $this->showArticleAddDialog = 1;
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'showArticleAddDialog' => $this->showArticleAddDialog,
            'availableLanguages' => LanguageFactory::getInstance()->getLanguages(),
        ]);
    }

    #[\Override]
    protected function createGridView(): ArticleGridView
    {
        return new ArticleGridView();
    }
}
