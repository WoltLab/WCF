<?php

namespace wcf\system\user\content\provider;

use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;

/**
 * User content provider for comments.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @extends AbstractDatabaseUserContentProvider<CommentList>
 */
class CommentUserContentProvider extends AbstractDatabaseUserContentProvider
{
    /**
     * @inheritdoc
     */
    #[\Override]
    public static function getDatabaseObjectClass()
    {
        return Comment::class;
    }
}
