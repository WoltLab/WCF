<?php

namespace wcf\data\article;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of cms articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template-covariant TDatabaseObject of Article|DatabaseObjectDecorator = Article
 * @extends DatabaseObjectList<TDatabaseObject>
 */
class ArticleList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Article::class;
}
