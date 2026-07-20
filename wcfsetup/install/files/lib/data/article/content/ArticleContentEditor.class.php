<?php

namespace wcf\data\article\content;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit article content.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `ArticleContentBuilder` instead.
 *
 * @mixin       ArticleContent
 * @extends DatabaseObjectEditor<ArticleContent>
 */
class ArticleContentEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ArticleContent::class;
}
