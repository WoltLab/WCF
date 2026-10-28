<?php

namespace wcf\data\object\type;

use wcf\data\object\type\definition\ObjectTypeDefinition;
use wcf\system\cache\eager\ObjectTypeRowCache;
use wcf\system\SingletonFactory;

/**
 * Manages the object type cache.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class ObjectTypeCache extends SingletonFactory
{
    /**
     * object type definition ids grouped by category name
     * @var array<string, list<int>>
     */
    private array $definitionsByCategory = [];

    /**
     * database rows of the object type definitions
     * @var array<int, array<string, mixed>>
     */
    private array $definitionRows = [];

    /**
     * object type definition ids by definition name
     * @var array<string, int>
     */
    private array $definitionIDs = [];

    /**
     * object type definitions created so far
     * @var array<int, ObjectTypeDefinition>
     */
    private array $definitions = [];

    /**
     * database rows of the object types
     * @var array<int, array<string, mixed>>
     */
    private array $objectTypeRows = [];

    /**
     * object type ids grouped by definition name and object type name
     * @var array<string, array<string, int>>
     */
    private array $objectTypeIDs = [];

    /**
     * object types created so far
     * @var array<int, ObjectType>
     */
    private array $objectTypes = [];

    /**
     * object types grouped by definition name, for the definitions requested so far
     * @var array<string, array<string, ObjectType>>
     */
    private array $groupedObjectTypes = [];

    #[\Override]
    protected function init()
    {
        $data = new ObjectTypeRowCache()->getCache();

        $this->definitionsByCategory = $data['categories'];
        $this->definitionRows = $data['definitions'];
        $this->definitionIDs = $data['definitionIDs'];
        $this->objectTypeRows = $data['objectTypes'];
        $this->objectTypeIDs = $data['objectTypeIDs'];
    }

    /**
     * Returns the object type definition with the given id or null if no such
     * object type definition exists.
     *
     * @return ?ObjectTypeDefinition
     */
    public function getDefinition(int $definitionID)
    {
        if (!isset($this->definitions[$definitionID])) {
            if (!isset($this->definitionRows[$definitionID])) {
                return null;
            }

            $this->definitions[$definitionID] = new ObjectTypeDefinition(null, $this->definitionRows[$definitionID]);
        }

        return $this->definitions[$definitionID];
    }

    /**
     * Returns the object type definition with the given name or null if no
     * such object type definition exists.
     *
     * @return ?ObjectTypeDefinition
     */
    public function getDefinitionByName(string $definitionName)
    {
        if (isset($this->definitionIDs[$definitionName])) {
            return $this->getDefinition($this->definitionIDs[$definitionName]);
        }

        return null;
    }

    /**
     * Returns a list of definitions by category name or 'null' if the given
     * category name is invalid.
     *
     * @return array<int, ObjectTypeDefinition>|null
     */
    public function getDefinitionsByCategory(string $categoryName)
    {
        if (isset($this->definitionsByCategory[$categoryName])) {
            $definitions = [];
            foreach ($this->definitionsByCategory[$categoryName] as $definitionID) {
                $definitions[$definitionID] = $this->getDefinition($definitionID);
            }

            return $definitions;
        }

        return null;
    }

    /**
     * Returns the object type with the given id or null if no such object type
     * exists.
     *
     * @return ?ObjectType
     */
    public function getObjectType(int $objectTypeID)
    {
        if (!isset($this->objectTypes[$objectTypeID])) {
            if (!isset($this->objectTypeRows[$objectTypeID])) {
                return null;
            }

            $this->objectTypes[$objectTypeID] = new ObjectType(null, $this->objectTypeRows[$objectTypeID]);
        }

        return $this->objectTypes[$objectTypeID];
    }

    /**
     * Returns the list of object type with the given definition name.
     *
     * @return array<string, ObjectType>
     */
    public function getObjectTypes(string $definitionName)
    {
        if (!isset($this->groupedObjectTypes[$definitionName])) {
            if (!isset($this->objectTypeIDs[$definitionName])) {
                return [];
            }

            $this->groupedObjectTypes[$definitionName] = \array_map(
                $this->getObjectType(...),
                $this->objectTypeIDs[$definitionName]
            );
        }

        return $this->groupedObjectTypes[$definitionName];
    }

    /**
     * Returns the object type with the given definition name and given name
     * or null of no such object type exists.
     *
     * @return ?ObjectType
     */
    public function getObjectTypeByName(string $definitionName, string $objectTypeName)
    {
        $objectTypeID = $this->getObjectTypeIDByName($definitionName, $objectTypeName);
        if ($objectTypeID !== null) {
            return $this->getObjectType($objectTypeID);
        }

        return null;
    }

    /**
     * Returns the object type id with the given definition name and given name.
     *
     * @return ?int
     */
    public function getObjectTypeIDByName(string $definitionName, string $objectTypeName)
    {
        return $this->objectTypeIDs[$definitionName][$objectTypeName] ?? null;
    }

    /**
     * Rebuilds the object type cache, the changes become visible with the next
     * request.
     *
     * @return void
     */
    public function resetCache()
    {
        new ObjectTypeRowCache()->rebuild();
    }
}
