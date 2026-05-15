<?php

namespace wcf\data\article\content;

use wcf\data\DatabaseObjectEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Provides functions to edit article content.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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

    /**
     * Returns whether the given slug is unique within the given language scope.
     * The `$excludedArticleID` is excluded from the lookup to allow updates of
     * an existing article.
     *
     * @since 6.3
     */
    public static function isUniqueSlug(string $slug, ?int $languageID, ?int $excludedArticleID = null): bool
    {
        if ($slug === '') {
            return true;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('slug = ?', [$slug]);

        if ($languageID === null) {
            $conditionBuilder->add('languageID IS NULL');
        } else {
            $conditionBuilder->add('(languageID = ? OR languageID IS NULL)', [$languageID]);
        }
        if ($excludedArticleID !== null) {
            $conditionBuilder->add('articleID <> ?', [$excludedArticleID]);
        }

        $sql = "SELECT  COUNT(*)
                FROM    wcf1_article_content
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchSingleColumn() === 0;
    }
}
