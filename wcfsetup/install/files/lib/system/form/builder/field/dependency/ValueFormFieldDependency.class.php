<?php

namespace wcf\system\form\builder\field\dependency;

use wcf\data\DatabaseObjectList;

/**
 * Represents a dependency that requires a field to have a certain value.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class ValueFormFieldDependency extends AbstractFormFieldDependency
{
    /**
     * is `true` if the field value may not have any of the set values and otherwise
     * `false`
     * @var bool
     */
    protected $negate = false;

    /**
     * possible values the field may have for the dependency to be met
     * @var null|array
     */
    protected $values;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_valueFormFieldDependency';

    /**
     * @inheritDoc
     */
    public function checkDependency()
    {
        if (\is_array($this->getField()->getValue())) {
            $check = false;
            // do not use `array_diff` because we use weak comparison
            foreach ($this->getValues() as $possibleValue) {
                foreach ($this->getField()->getValue() as $actualValue) {
                    if ($possibleValue == $actualValue) {
                        $check = true;
                        break;
                    }
                }
            }
        } else {
            $check = \in_array($this->getField()->getValue(), $this->getValues());
        }

        if ($this->isNegated()) {
            return !$check;
        }

        return $check;
    }

    /**
     * Returns the possible values the field may have for the dependency to be met.
     *
     * @return  array               possible field values
     *
     * @throws  \BadMethodCallException     if no values have been set
     */
    public function getValues()
    {
        if ($this->values === null) {
            throw new \BadMethodCallException("Values have not been set for dependency '{$this->getId()}' on node '{$this->getDependentNode()->getId()}'.");
        }

        return $this->values;
    }

    /**
     * Returns `true` if the field value may not have any of the set values and
     * otherwise `false`.
     *
     * @return  bool
     */
    public function isNegated()
    {
        return $this->negate;
    }

    /**
     * Sets if the field value may not have any of the set values.
     *
     * @param bool $negate
     * @return  static      $this       this dependency
     */
    public function negate($negate = true)
    {
        $this->negate = $negate;

        return $this;
    }

    /**
     * Sets the possible values the field may have for the dependency to be met.
     *
     * @param array|callable|DatabaseObjectList $values possible field values
     * @return  static      $this                   this dependency
     *
     * @throws  \InvalidArgumentException               if given value are no array, callable, DatabaseObjectList, or otherwise invalid
     * @throws  \UnexpectedValueException               if callable does not return an array or a DatabaseObjectList
     */
    public function values($values)
    {
        if (!\is_array($values) && !\is_callable($values) && !($values instanceof DatabaseObjectList)) {
            throw new \InvalidArgumentException("The given values are neither an array, a callable nor an instance of '" . DatabaseObjectList::class . "', " . \gettype($values) . " given.");
        }

        if (\is_callable($values)) {
            $values = $values();

            if (!\is_array($values) && !($values instanceof DatabaseObjectList)) {
                throw new \UnexpectedValueException("The values callable is expected to return an array or an instance of '" . DatabaseObjectList::class . "', " . \gettype($values) . " returned.");
            }
        }

        if ($values instanceof DatabaseObjectList) {
            // automatically read objects
            if ($values->objectIDs === null) {
                $values->readObjects();
            }

            $dboValues = [];
            foreach ($values as $object) {
                if (!$object::getDatabaseTableIndexIsIdentity()) {
                    throw new \InvalidArgumentException("The database objects in the passed list must must have an index that identifies the objects.");
                }

                $dboValues[] = $object->getObjectID();
            }

            $values = $dboValues;
        }

        if (empty($values)) {
            throw new \InvalidArgumentException("Given values are empty.");
        }
        foreach ($values as $value) {
            if (!\is_string($value) && !\is_numeric($value)) {
                throw new \InvalidArgumentException("Values contains invalid value of type '" . \gettype($value) . "', only strings or numbers are allowed.");
            }
        }

        $this->values = $values;

        return $this;
    }
}
