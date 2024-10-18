<?php

namespace wcf\data;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Abstract class for a list of database objects.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class DatabaseObjectList implements \Countable, ITraversableObject
{
    /**
     * class name
     * @var string
     */
    public $className = '';

    /**
     * class name of the object decorator; if left empty, no decorator is used
     * @var string
     */
    public $decoratorClassName = '';

    /**
     * object class name
     * @var string
     */
    public $objectClassName = '';

    /**
     * result objects
     * @var DatabaseObject[]
     */
    public $objects = [];

    /**
     * ids of result objects
     * @var int[]
     */
    public $objectIDs;

    /**
     * sql offset
     * @var int
     */
    public $sqlOffset = 0;

    /**
     * sql limit
     * @var int
     */
    public $sqlLimit = 0;

    /**
     * sql order by statement
     * @var string
     */
    public $sqlOrderBy = '';

    /**
     * sql select parameters
     * @var string
     */
    public $sqlSelects = '';

    /**
     * sql select joins which are necessary for where statements
     * @var string
     */
    public $sqlConditionJoins = '';

    /**
     * sql select joins
     * @var string
     */
    public $sqlJoins = '';

    /**
     * enables the automatic usage of the qualified shorthand
     * @var bool
     */
    public $useQualifiedShorthand = true;

    /**
     * sql conditions
     * @var PreparedStatementConditionBuilder
     */
    protected $conditionBuilder;

    /**
     * current iterator index
     * @var int
     */
    protected $index = 0;

    /**
     * list of index to object relation
     * @var int[]
     */
    protected $indexToObject = [];

    /**
     * Creates a new DatabaseObjectList object.
     */
    public function __construct()
    {
        // set class name
        if (empty($this->className)) {
            $className = static::class;

            if (\mb_substr($className, -4) == 'List') {
                $this->className = \mb_substr($className, 0, -4);
            }
        }

        if (!empty($this->decoratorClassName)) {
            // validate decorator class name
            if (!\is_subclass_of($this->decoratorClassName, DatabaseObjectDecorator::class)) {
                throw new SystemException("'" . $this->decoratorClassName . "' should extend '" . DatabaseObjectDecorator::class . "'");
            }

            $objectClassName = $this->objectClassName ?: $this->className;
            $baseClassName = \call_user_func([$this->decoratorClassName, 'getBaseClass']);
            if ($objectClassName != $baseClassName && !\is_subclass_of($objectClassName, $baseClassName)) {
                throw new SystemException("'" . $this->decoratorClassName . "' can't decorate objects of class '" . $objectClassName . "'");
            }
        }

        $this->conditionBuilder = new PreparedStatementConditionBuilder();

        EventHandler::getInstance()->fireAction($this, 'init');
    }

    /**
     * Counts the number of objects.
     *
     * @return  int
     */
    public function countObjects()
    {
        $sql = "SELECT  COUNT(*)
                FROM    " . $this->getDatabaseTableName() . " " . $this->getDatabaseTableAlias() . "
                " . $this->sqlConditionJoins . "
                " . $this->getConditionBuilder();
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($this->getConditionBuilder()->getParameters());

        return $statement->fetchSingleColumn();
    }

    /**
     * Reads the object ids from database.
     */
    public function readObjectIDs()
    {
        $this->objectIDs = [];
        $sql = "SELECT  " . $this->getDatabaseTableAlias() . "." . $this->getDatabaseTableIndexName() . " AS objectID
                FROM    " . $this->getDatabaseTableName() . " " . $this->getDatabaseTableAlias() . "
                " . $this->sqlConditionJoins . "
                " . $this->getConditionBuilder() . "
                " . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
        $statement = WCF::getDB()->prepare($sql, $this->sqlLimit, $this->sqlOffset);
        $statement->execute($this->getConditionBuilder()->getParameters());
        $this->objectIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Reads the objects from database.
     */
    public function readObjects()
    {
        if ($this->objectIDs !== null) {
            if (empty($this->objectIDs)) {
                return;
            }

            $objectIdPlaceholder = "?" . \str_repeat(',?', \count($this->objectIDs) - 1);

            $sql = "SELECT  " . (!empty($this->sqlSelects) ? $this->sqlSelects . ($this->useQualifiedShorthand ? ',' : '') : '') . "
                            " . ($this->useQualifiedShorthand ? $this->getDatabaseTableAlias() . '.*' : '') . "
                    FROM    " . $this->getDatabaseTableName() . " " . $this->getDatabaseTableAlias() . "
                            " . $this->sqlJoins . "
                    WHERE   " . $this->getDatabaseTableAlias() . "." . $this->getDatabaseTableIndexName() . " IN ({$objectIdPlaceholder})
                            " . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($this->objectIDs);
            $this->objects = $statement->fetchObjects(($this->objectClassName ?: $this->className));
        } else {
            $sql = "SELECT  " . (!empty($this->sqlSelects) ? $this->sqlSelects . ($this->useQualifiedShorthand ? ',' : '') : '') . "
                            " . ($this->useQualifiedShorthand ? $this->getDatabaseTableAlias() . '.*' : '') . "
                    FROM    " . $this->getDatabaseTableName() . " " . $this->getDatabaseTableAlias() . "
                    " . $this->sqlJoins . "
                    " . $this->getConditionBuilder() . "
                    " . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
            $statement = WCF::getDB()->prepare($sql, $this->sqlLimit, $this->sqlOffset);
            $statement->execute($this->getConditionBuilder()->getParameters());
            $this->objects = $statement->fetchObjects(($this->objectClassName ?: $this->className));
        }

        // decorate objects
        if (!empty($this->decoratorClassName)) {
            foreach ($this->objects as &$object) {
                $object = new $this->decoratorClassName($object);
            }
            unset($object);
        }

        // use table index as array index
        $objects = $this->indexToObject = [];
        foreach ($this->objects as $object) {
            $objectID = $object->getObjectID();
            $objects[$objectID] = $object;

            $this->indexToObject[] = $objectID;
        }
        $this->objectIDs = $this->indexToObject;
        $this->objects = $objects;
    }

    /**
     * Returns the object ids of the list.
     *
     * @return  int[]
     */
    public function getObjectIDs()
    {
        return $this->objectIDs;
    }

    /**
     * Sets the object ids.
     *
     * @param int[] $objectIDs
     */
    public function setObjectIDs(array $objectIDs)
    {
        $this->objectIDs = \array_merge($objectIDs);
    }

    /**
     * Returns the objects of the list.
     *
     * @return  DatabaseObject[]
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Returns the condition builder object.
     *
     * @return  PreparedStatementConditionBuilder
     */
    public function getConditionBuilder()
    {
        return $this->conditionBuilder;
    }

    /**
     * Sets the condition builder dynamically.
     *
     * @param PreparedStatementConditionBuilder $conditionBuilder
     * @since   5.3
     */
    public function setConditionBuilder(PreparedStatementConditionBuilder $conditionBuilder)
    {
        $this->conditionBuilder = $conditionBuilder;
    }

    /**
     * Returns the name of the database table.
     *
     * @return  string
     */
    public function getDatabaseTableName()
    {
        return \call_user_func([$this->className, 'getDatabaseTableName']);
    }

    /**
     * Returns the name of the database table.
     *
     * @return  string
     */
    public function getDatabaseTableIndexName()
    {
        return \call_user_func([$this->className, 'getDatabaseTableIndexName']);
    }

    /**
     * Returns the name of the database table alias.
     *
     * @return  string
     */
    public function getDatabaseTableAlias()
    {
        return \call_user_func([$this->className, 'getDatabaseTableAlias']);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->objects);
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $objectID = $this->indexToObject[$this->index];

        return $this->objects[$objectID];
    }

    /**
     * CAUTION: This methods does not return the current iterator index,
     * but the object key which maps to that index.
     *
     * @see \Iterator::key()
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->indexToObject[$this->index];
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->indexToObject[$this->index]);
    }

    /**
     * @inheritDoc
     */
    public function seek($offset): void
    {
        $this->index = $offset;

        if (!$this->valid()) {
            throw new \OutOfBoundsException();
        }
    }

    /**
     * @inheritDoc
     */
    public function seekTo($objectID)
    {
        $this->index = \array_search($objectID, $this->indexToObject);

        if ($this->index === false) {
            throw new SystemException("object id '" . $objectID . "' is invalid");
        }
    }

    /**
     * @inheritDoc
     */
    public function search($objectID)
    {
        try {
            $this->seekTo($objectID);

            return $this->current();
        } catch (SystemException $e) {
            return null;
        }
    }

    /**
     * Returns the only object in this list or `null` if the list is empty.
     *
     * @return  DatabaseObject|null
     * @throws  \BadMethodCallException     if list contains more than one object
     */
    public function getSingleObject()
    {
        if (\count($this->objects) > 1) {
            throw new \BadMethodCallException("Cannot get a single object when the list contains " . \count($this->objects) . " objects.");
        }

        if (empty($this->objects)) {
            return null;
        }

        return \reset($this->objects);
    }
}
