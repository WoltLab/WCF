<?php

namespace wcf\data\condition;

use wcf\data\DatabaseObject;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Represents a condition.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $conditionID        unique id of the condition
 * @property-read   int $objectTypeID       id of the condition object type (of different condition object type definitions)
 * @property-read   int $objectID       id of the conditioned object of the object type represented by `$objectTypeID`
 * @property-read   array $conditionData      array with the condition data with is processed by the condition object type's processor
 */
class Condition extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        $value = parent::__get($name);

        // treat condition data as data variables if it is an array
        if ($value === null && \is_array($this->data['conditionData']) && isset($this->data['conditionData'][$name])) {
            $value = $this->data['conditionData'][$name];
        }

        return $value;
    }

    /**
     * Returns the condition object type of the condition.
     *
     * @return  \wcf\data\object\type\ObjectType
     */
    public function getObjectType()
    {
        return ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
    }

    /**
     * @inheritDoc
     */
    protected function handleData($data)
    {
        parent::handleData($data);

        // handle condition data
        $this->data['conditionData'] = @\unserialize($data['conditionData']);
        if (!\is_array($this->data['conditionData'])) {
            $this->data['conditionData'] = [];
        }
    }

    /**
     * @inheritDoc
     */
    public static function getDatabaseTableAlias()
    {
        return 'condition_table';
    }
}
