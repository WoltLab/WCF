<?php

namespace wcf\data;

use wcf\system\exception\SystemException;

/**
 * Basic implementation for object decorators.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template TDatabaseObject of DatabaseObject
 */
abstract class DatabaseObjectDecorator extends DatabaseObject
{
    /**
     * name of the base class
     * @var string
     */
    protected static $baseClass = '';

    /**
     * decorated object
     * @var TDatabaseObject
     */
    protected $object;

    /**
     * @param TDatabaseObject $object
     * @throws SystemException
     */
    public function __construct(DatabaseObject $object)
    {
        if (empty(static::$baseClass)) {
            throw new SystemException('Base class not specified');
        }

        if (!($object instanceof static::$baseClass)) {
            throw new SystemException("Object does not match '" . static::$baseClass . "' (given object is of class '" . \get_class($object) . "')");
        }

        $this->object = $object;
    }

    #[\Override]
    public function __get(string $name)
    {
        return $this->object->__get($name);
    }

    #[\Override]
    public function __isset(string $name)
    {
        return $this->object->__isset($name);
    }

    #[\Override]
    public function getObjectID()
    {
        return $this->object->getObjectID();
    }

    #[\Override]
    public function getData()
    {
        return $this->object->getData();
    }

    #[\Override]
    public function isNil(): bool
    {
        return $this->object->isNil();
    }

    /**
     * Delegates inaccessible methods calls to the decorated object.
     *
     * @param mixed[] $arguments
     * @return mixed
     * @throws SystemException
     */
    public function __call(string $name, array $arguments)
    {
        if (!\method_exists($this->object, $name) && !($this->object instanceof self)) {
            throw new SystemException("unknown method '" . $name . "'");
        }

        return \call_user_func_array([$this->object, $name], $arguments);
    }

    #[\Override]
    public static function getDatabaseTableAlias()
    {
        return \call_user_func([static::$baseClass, 'getDatabaseTableAlias']);
    }

    #[\Override]
    public static function getDatabaseTableName()
    {
        return \call_user_func([static::$baseClass, 'getDatabaseTableName']);
    }

    #[\Override]
    public static function getDatabaseTableIndexIsIdentity()
    {
        return \call_user_func([static::$baseClass, 'getDatabaseTableIndexIsIdentity']);
    }

    #[\Override]
    public static function getDatabaseTableIndexName()
    {
        return \call_user_func([static::$baseClass, 'getDatabaseTableIndexName']);
    }

    /**
     * Returns the name of the base class.
     *
     * @return string
     */
    public static function getBaseClass()
    {
        return static::$baseClass;
    }

    /**
     * Returns the decorated object
     *
     * @return TDatabaseObject
     */
    public function getDecoratedObject()
    {
        return $this->object;
    }
}
