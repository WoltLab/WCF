<?php

namespace wcf\data\article\content;

use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a viewable article content.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `ArticleContent` instead.
 *
 * @mixin   ArticleContent
 * @extends DatabaseObjectDecorator<ArticleContent>
 */
class ViewableArticleContent extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ArticleContent::class;

    /**
     * Returns a specific article content decorated as viewable article content.
     *
     * @return ?ViewableArticleContent
     */
    public static function getArticleContent(int $articleContentID)
    {
        $list = new ViewableArticleContentList();
        $list->setObjectIDs([$articleContentID]);
        $list->readObjects();

        return $list->search($articleContentID);
    }
}
