<?php

namespace wcf\system\cache\eager;

use wcf\system\WCF;

/**
 * Eager cache of the database rows of object types and object type definitions.
 * It consists of plain arrays only, which allows the OPcache to keep it in
 * shared memory, the objects are created on demand by `ObjectTypeCache`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @extends AbstractEagerCache<array{
 *     categories: array<string, list<int>>,
 *     definitions: array<int, array<string, mixed>>,
 *     definitionIDs: array<string, int>,
 *     objectTypes: array<int, array<string, mixed>>,
 *     objectTypeIDs: array<string, array<string, int>>,
 * }>
 */
final class ObjectTypeRowCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): array
    {
        $data = [
            'categories' => [],
            'definitions' => [],
            'definitionIDs' => [],
            'objectTypes' => [],
            'objectTypeIDs' => [],
        ];

        $sql = "SELECT  *
                FROM    wcf1_object_type_definition";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $data['definitions'][$row['definitionID']] = $row;
            $data['definitionIDs'][$row['definitionName']] = $row['definitionID'];

            if ($row['categoryName'] !== '') {
                if (!isset($data['categories'][$row['categoryName']])) {
                    $data['categories'][$row['categoryName']] = [];
                }

                $data['categories'][$row['categoryName']][] = $row['definitionID'];
            }
        }

        $sql = "SELECT  *
                FROM    wcf1_object_type";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $data['objectTypes'][$row['objectTypeID']] = $row;

            $definitionName = $data['definitions'][$row['definitionID']]['definitionName'];
            if (!isset($data['objectTypeIDs'][$definitionName])) {
                $data['objectTypeIDs'][$definitionName] = [];
            }
            $data['objectTypeIDs'][$definitionName][$row['objectType']] = $row['objectTypeID'];
        }

        return $data;
    }
}
