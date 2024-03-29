<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\system\exception\InvalidObjectArgument;

/**
 * Abstract condition implementation for check a text-typed property of a database
 * object.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
abstract class AbstractObjectTextPropertyCondition extends AbstractTextCondition implements
    IObjectCondition,
    IObjectListCondition
{
    /**
     * name of the relevant database object class
     * @var string
     */
    protected $className = '';

    /**
     * is true if the entered value should be split by commas to search for
     * multiple values
     * @var bool
     */
    protected $supportsMultipleValues = false;

    /**
     * name of the relevant object property
     * @var string
     */
    protected $propertyName = '';

    /**
     * @inheritDoc
     */
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        $className = $this->getListClassName();
        if (!($objectList instanceof $className)) {
            throw new InvalidObjectArgument($objectList, $className, 'Object list');
        }

        if ($this->supportsMultipleValues) {
            $objectList->getConditionBuilder()->add(
                $objectList->getDatabaseTableAlias() . '.' . $this->getPropertyName() . ' IN (?)',
                [$conditionData[$this->fieldName]]
            );
        } else {
            $objectList->getConditionBuilder()->add(
                $objectList->getDatabaseTableAlias() . '.' . $this->getPropertyName() . ' = ?',
                [$conditionData[$this->fieldName]]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function checkObject(DatabaseObject $object, array $conditionData)
    {
        $className = $this->getClassName();
        if (!($object instanceof $className)) {
            throw new InvalidObjectArgument($object, $className);
        }

        return \in_array($object->{$this->getPropertyName()}, $conditionData[$this->fieldName]);
    }

    /**
     * Returns the name of the relevant database object class.
     *
     * @return  string
     */
    protected function getClassName()
    {
        return $this->className;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $value = parent::getData();
        if ($value === null || !$this->supportsMultipleValues) {
            return $value;
        }

        return [
            $this->fieldName => \preg_split('/\s*,\s*/', $value[$this->fieldName], -1, \PREG_SPLIT_NO_EMPTY),
        ];
    }

    /**
     * Returns the name of the relevant database object list class.
     *
     * @return  string
     */
    protected function getListClassName()
    {
        return $this->className . 'List';
    }

    /**
     * Returns the name of the relevant object property.
     *
     * @return  string
     */
    protected function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @inheritDoc
     */
    public function setData(Condition $condition)
    {
        parent::setData($condition);

        if ($this->supportsMultipleValues) {
            /** @noinspection PhpParamsInspection */
            $this->fieldValue = \implode(',', $this->fieldValue);
        }
    }
}
