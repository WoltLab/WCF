<?php

namespace wcf\system\cache\builder;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the available label group ids for article categories.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.1
 */
class ArticleCategoryLabelCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('objectTypeID = ?', [
            ObjectTypeCache::getInstance()->getObjectTypeByName(
                'com.woltlab.wcf.label.objectType',
                'com.woltlab.wcf.article.category'
            )->objectTypeID,
        ]);
        $conditionBuilder->add(
            'objectID IN (
                SELECT  categoryID
                FROM    wcf1_category
                WHERE   objectTypeID = ?
            )',
            [CategoryHandler::getInstance()->getObjectTypeByName('com.woltlab.wcf.article.category')->objectTypeID]
        );

        $sql = "SELECT  groupID, objectID
                FROM    wcf1_label_group_to_object
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        return $statement->fetchMap('objectID', 'groupID', false);
    }
}
