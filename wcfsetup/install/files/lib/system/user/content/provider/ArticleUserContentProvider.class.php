<?php

namespace wcf\system\user\content\provider;

use wcf\data\article\Article;
use wcf\data\article\ArticleList;

/**
 * User content provider for articles.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @extends AbstractDatabaseUserContentProvider<ArticleList>
 */
class ArticleUserContentProvider extends AbstractDatabaseUserContentProvider
{
    /**
     * @inheritdoc
     */
    #[\Override]
    public static function getDatabaseObjectClass()
    {
        return Article::class;
    }
}
