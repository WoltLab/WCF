<?php

namespace wcf\data\object\type;

use wcf\data\object\type\definition\ObjectTypeDefinition;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\ProcessibleDatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\exception\ClassNotFoundException;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Represents an object type.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $objectTypeID       unique id of the object type
 * @property-read   int $definitionID       id of the object type definition the object type belongs to
 * @property-read   int $packageID      id of the package the which delivers the object type
 * @property-read   string $objectType     textual identifier of the object type
 * @property-read   string $className      PHP class name of the object type processor (implementing the interface forced by the object type definition)
 * @property-read   mixed[] $additionalData     array with additional data of the object type
 */
class ObjectType extends ProcessibleDatabaseObject
{
    use TDatabaseObjectOptions;
    use TDatabaseObjectPermissions;

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'objectTypeID';

    #[\Override]
    public function __get(string $name)
    {
        $value = parent::__get($name);

        // treat additional data as data variables if it is an array
        if ($value === null) {
            if (\is_array($this->data['additionalData']) && isset($this->data['additionalData'][$name])) {
                $value = $this->data['additionalData'][$name];
            }
        }

        return $value;
    }

    #[\Override]
    public function __isset(string $name)
    {
        $value = parent::__isset($name);

        if (!$value && \is_array($this->data['additionalData']) && isset($this->data['additionalData'][$name])) {
            return true;
        }

        return $value;
    }

    /**
     * Returns the names of properties that should be serialized.
     *
     * @return  string[]
     */
    final public function __sleep(): array
    {
        // 'processor' isn't returned since it can be an instance of
        // wcf\system\SingletonFactory which may not be serialized
        return ['data'];
    }

    #[\Override]
    protected function handleData(array $data)
    {
        parent::handleData($data);

        $this->data['additionalData'] = @\unserialize($this->data['additionalData']);
        if (!\is_array($this->data['additionalData'])) {
            $this->data['additionalData'] = [];
        }
    }

    #[\Override]
    public function getProcessor()
    {
        if ($this->processor === null) {
            if ($this->className !== '') {
                if (!\class_exists($this->className)) {
                    throw new ClassNotFoundException($this->className);
                }

                $definitionInterface = ObjectTypeCache::getInstance()
                    ->getDefinition($this->definitionID)
                    ->interfaceName;
                if ($definitionInterface !== '') {
                    if (!\interface_exists($definitionInterface)) {
                        throw new SystemException("Unable to find interface '" . $definitionInterface . "'");
                    }

                    if (!\is_subclass_of($this->className, $definitionInterface)) {
                        throw new ImplementationException($this->className, $definitionInterface);
                    }
                }

                if (\is_subclass_of($this->className, SingletonFactory::class)) {
                    $this->processor = \call_user_func([$this->className, 'getInstance']);
                } else {
                    $this->processor = new $this->className($this);
                }
            }
        }

        return $this->processor;
    }

    /**
     * Returns the object type definition of the object type.
     *
     * @return  ObjectTypeDefinition
     */
    public function getDefinition()
    {
        return ObjectTypeCache::getInstance()->getDefinition($this->definitionID);
    }

    /**
     * Returns the package that this object type belongs to.
     *
     * @return      Package
     * @since       5.2
     */
    public function getPackage()
    {
        return PackageCache::getInstance()->getPackage($this->packageID);
    }
}
