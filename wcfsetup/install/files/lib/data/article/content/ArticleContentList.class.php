<?php

namespace wcf\data\article\content;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of article content.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template TDatabaseObject of ArticleContent|DatabaseObjectDecorator<ArticleContent> = ArticleContent
 * @extends DatabaseObjectList<TDatabaseObject>
 */
class ArticleContentList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = ArticleContent::class;
}
