<?php

namespace wcf\system\search;

use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeList;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default implementation for search index managers, this class should be extended by
 * all search index managers to preserve compatibility in case of interface changes.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractSearchIndexManager extends SingletonFactory implements ISearchIndexManager
{
    /**
     * @inheritDoc
     */
    public function createSearchIndices()
    {
        // get definition id
        $sql = "SELECT  definitionID
                FROM    wcf1_object_type_definition
                WHERE   definitionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(['com.woltlab.wcf.searchableObjectType']);
        $row = $statement->fetchArray();

        $objectTypeList = new ObjectTypeList();
        $objectTypeList->getConditionBuilder()->add("object_type.definitionID = ?", [$row['definitionID']]);
        $objectTypeList->readObjects();

        foreach ($objectTypeList as $objectType) {
            $this->createSearchIndex($objectType);
        }
    }

    /**
     * Creates the search index for given object type.
     */
    abstract protected function createSearchIndex(ObjectType $objectType);

    /**
     * @inheritDoc
     */
    public function beginBulkOperation()
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    public function commitBulkOperation()
    {
        // does nothing
    }
}
