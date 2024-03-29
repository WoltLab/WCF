<?php

namespace wcf\data\article;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of cms articles.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method  Article     current()
 * @method  Article[]       getObjects()
 * @method  Article|null    getSingleObject()
 * @method  Article|null    search($objectID)
 * @property    Article[] $objects
 */
class ArticleList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Article::class;
}
