<?php

namespace wcf\data;

/**
 * Provides legacy access to the properties of the related user profile object.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\User
 * @since   3.0
 * @deprecated  3.0
 */
trait TLegacyUserPropertyAccess
{
    /**
     * Returns the value of an object data variable with the given name.
     *
     * @param string $name
     * @return  mixed
     * @see \wcf\data\IStorableObject::__get()
     *
     */
    public function __get($name)
    {
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $value = parent::__get($name);
        if ($value !== null) {
            return $value;
        } elseif (!($this instanceof DatabaseObjectDecorator) && \array_key_exists($name, $this->data)) {
            return;
        } elseif ($this instanceof DatabaseObjectDecorator && \array_key_exists($name, $this->object->data)) {
            return;
        }

        // in case any code should rely on directly accessing user properties,
        // refer them to the user profile object
        /** @noinspection PhpVariableVariableInspection */
        return $this->getUserProfile()->{$name};
    }
}
