<?php

namespace wcf\data\object\type;

use wcf\data\object\type\definition\ObjectTypeDefinition;
use wcf\system\cache\builder\ObjectTypeCacheBuilder;
use wcf\system\SingletonFactory;

/**
 * Manages the object type cache.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ObjectTypeCache extends SingletonFactory
{
    /**
     * object type definitions
     * @var ObjectTypeDefinition[]
     */
    protected $definitions = [];

    /**
     * object type definition ids grouped by category name
     * @var int[][]
     */
    protected $definitionsByCategory = [];

    /**
     * object type definitions sorted by name
     * @var ObjectTypeDefinition[]
     */
    protected $definitionsByName = [];

    /**
     * object types
     * @var ObjectType[]
     */
    protected $objectTypes = [];

    /**
     * object types grouped by definition
     * @var array
     */
    protected $groupedObjectTypes = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        // get definition cache
        $this->definitionsByCategory = ObjectTypeCacheBuilder::getInstance()->getData([], 'categories');
        $this->definitions = ObjectTypeCacheBuilder::getInstance()->getData([], 'definitions');
        foreach ($this->definitions as $definition) {
            $this->definitionsByName[$definition->definitionName] = $definition;
        }

        // get object type cache
        $this->objectTypes = ObjectTypeCacheBuilder::getInstance()->getData([], 'objectTypes');
        $this->groupedObjectTypes = ObjectTypeCacheBuilder::getInstance()->getData([], 'groupedObjectTypes');
    }

    /**
     * Returns the object type definition with the given id or null if no such
     * object type definition exists.
     *
     * @param int $definitionID
     * @return  ObjectTypeDefinition|null
     */
    public function getDefinition($definitionID)
    {
        return $this->definitions[$definitionID] ?? null;
    }

    /**
     * Returns the object type definition with the given name or null if no
     * such object type definition exists.
     *
     * @param string $definitionName
     * @return  ObjectTypeDefinition|null
     */
    public function getDefinitionByName($definitionName)
    {
        return $this->definitionsByName[$definitionName] ?? null;
    }

    /**
     * Returns a list of definitions by category name or 'null' if the given
     * category name is invalid.
     *
     * @param string $categoryName
     * @return  ObjectTypeDefinition[]|null
     */
    public function getDefinitionsByCategory($categoryName)
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
     * @param int $objectTypeID
     * @return  ObjectType|null
     */
    public function getObjectType($objectTypeID)
    {
        return $this->objectTypes[$objectTypeID] ?? null;
    }

    /**
     * Returns the list of object type with the given definition name.
     *
     * @param string $definitionName
     * @return  ObjectType[]
     */
    public function getObjectTypes($definitionName)
    {
        if (isset($this->groupedObjectTypes[$definitionName])) {
            return $this->groupedObjectTypes[$definitionName];
        }

        return [];
    }

    /**
     * Returns the object type with the given definition name and given name
     * or null of no such object type exists.
     *
     * @param string $definitionName
     * @param string $objectTypeName
     * @return  ObjectType|null
     */
    public function getObjectTypeByName($definitionName, $objectTypeName)
    {
        if (
            isset($this->groupedObjectTypes[$definitionName])
            && isset($this->groupedObjectTypes[$definitionName][$objectTypeName])
        ) {
            return $this->groupedObjectTypes[$definitionName][$objectTypeName];
        }

        return null;
    }

    /**
     * Returns the object type id with the given definition name and given name.
     *
     * @param string $definitionName
     * @param string $objectTypeName
     * @return  int|null
     */
    public function getObjectTypeIDByName($definitionName, $objectTypeName)
    {
        $objectType = $this->getObjectTypeByName($definitionName, $objectTypeName);
        if ($objectType !== null) {
            return $objectType->objectTypeID;
        }

        return null;
    }

    /**
     * Resets and reloads the object type cache.
     */
    public function resetCache()
    {
        ObjectTypeCacheBuilder::getInstance()->reset();
        $this->init();
    }
}
