<?php

namespace wcf\util;

use wcf\data\DatabaseObjectDecorator;
use wcf\system\exception\SystemException;

/**
 * Provides methods for class interactions.
 *
 * @author  Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class ClassUtil
{
    /**
     * Checks whether the given objects are equal.
     * Objects are considered equal, when they are instances of the same class and all attributes are equal.
     *
     * @param object $a
     * @param object $b
     * @return  bool
     */
    public static function equals($a, $b)
    {
        // @phpstan-ignore function.alreadyNarrowedType
        if (!\is_object($a)) {
            return false;
        }

        return \print_r($a, true) === \print_r($b, true);
    }

    /**
     * Checks whether given class extends or implements the target class or interface.
     * You SHOULD NOT call this method if 'instanceof' satisfies your request!
     *
     * @param string $className
     * @param string $targetClass
     * @return  bool
     * @throws  SystemException
     *
     * @deprecated  use is_subclass_of() instead
     */
    public static function isInstanceOf($className, $targetClass)
    {
        // validate parameters
        // @phpstan-ignore function.alreadyNarrowedType
        if (!\is_string($className)) {
            return false;
        } elseif (!\class_exists($className)) {
            throw new SystemException(
                "Cannot determine class inheritance, class '" . $className . "' does not exist"
            );
        } elseif (!\class_exists($targetClass) && !\interface_exists($targetClass)) {
            throw new SystemException(
                "Cannot determine class inheritance, reference class '" . $targetClass . "' does not exist"
            );
        }

        return \is_subclass_of($className, $targetClass);
    }

    /**
     * Returns `true` if the given class extends or implements the target class
     * or interface or if the given class is database object decorator and the
     * decorated class extends or implements the target class.
     *
     * This method also supports decorated decorators.
     *
     * @param object|string $className checked class
     * @param string $targetClass target class or interface
     * @return  bool
     */
    public static function isDecoratedInstanceOf($className, $targetClass)
    {
        if (\is_subclass_of($className, $targetClass)) {
            return true;
        }

        $parentClass = new \ReflectionClass($className);
        do {
            $className = $parentClass->name;

            if (!\is_subclass_of($className, DatabaseObjectDecorator::class)) {
                return false;
            }

            if (\is_subclass_of($className::getBaseClass(), $targetClass)) {
                return true;
            }
        } while (($parentClass = $parentClass->getParentClass()));

        return false;
    }

    /**
     * Returns the properties as a key-value array to construct the given object.
     *
     * @return array<string, mixed>
     */
    public static function getConstructorProperties(object $object): array
    {
        $reflection = new \ReflectionClass($object);

        $properties = [];
        foreach ($reflection->getConstructor()?->getParameters() ?? [] as $parameter) {
            $property = $reflection->getProperty($parameter->getName());

            if (!$property->isInitialized($object)) {
                continue;
            }

            $properties[$property->getName()] = $property->getValue($object);
        }

        return $properties;
    }

    /**
     * Forbid creation of ClassUtil objects.
     */
    private function __construct()
    {
        // does nothing
    }
}
