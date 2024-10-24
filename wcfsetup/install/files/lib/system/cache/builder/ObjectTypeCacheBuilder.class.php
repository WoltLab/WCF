<?php

namespace wcf\system\cache\builder;

use wcf\data\object\type\definition\ObjectTypeDefinition;
use wcf\data\object\type\ObjectType;
use wcf\system\WCF;

/**
 * Caches object types and object type definitions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ObjectTypeCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $data = [
            'categories' => [],
            'definitions' => [],
            'objectTypes' => [],
            'groupedObjectTypes' => [],
        ];

        // get definitions
        $sql = "SELECT  *
                FROM    wcf1_object_type_definition";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $data['definitions'][$row['definitionID']] = new ObjectTypeDefinition(null, $row);

            if ($row['categoryName']) {
                if (!isset($data['categories'][$row['categoryName']])) {
                    $data['categories'][$row['categoryName']] = [];
                }

                $data['categories'][$row['categoryName']][] = $row['definitionID'];
            }
        }

        // get object types
        $sql = "SELECT  *
                FROM    wcf1_object_type object_type";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $data['objectTypes'][$row['objectTypeID']] = $objectType = new ObjectType(null, $row);

            $definition = $data['definitions'][$objectType->definitionID];
            if (!isset($data['groupedObjectTypes'][$definition->definitionName])) {
                $data['groupedObjectTypes'][$definition->definitionName] = [];
            }
            $data['groupedObjectTypes'][$definition->definitionName][$objectType->objectType] = $objectType;
        }

        return $data;
    }
}
