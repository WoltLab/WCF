<?php

namespace wcf\system\cache\builder;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\eager\ObjectTypeRowCache;

/**
 * Caches object types and object type definitions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.3 use `ObjectTypeCache` instead
 */
class ObjectTypeCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    public function rebuild(array $parameters): array
    {
        $cache = new ObjectTypeRowCache()->getCache();
        $objectTypeCache = ObjectTypeCache::getInstance();

        $data = [
            'categories' => $cache['categories'],
            'definitions' => [],
            'objectTypes' => [],
            'groupedObjectTypes' => [],
        ];

        foreach (\array_keys($cache['definitions']) as $definitionID) {
            $data['definitions'][$definitionID] = $objectTypeCache->getDefinition($definitionID);
        }

        foreach (\array_keys($cache['objectTypes']) as $objectTypeID) {
            $data['objectTypes'][$objectTypeID] = $objectTypeCache->getObjectType($objectTypeID);
        }

        foreach (\array_keys($cache['objectTypeIDs']) as $definitionName) {
            $data['groupedObjectTypes'][$definitionName] = $objectTypeCache->getObjectTypes($definitionName);
        }

        return $data;
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        new ObjectTypeRowCache()->rebuild();
    }
}
