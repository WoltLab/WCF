<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;
use wcf\data\condition\ConditionAction;
use wcf\data\condition\ConditionList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\condition\provider\AbstractConditionProvider;
use wcf\system\condition\type\IConditionType;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Handles general condition-related matters.
 *
 * @author Olaf Braun, Matthias Schmidt
 * @copyright   2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @phpstan-type ConditionValue float|int|string
 */
final class ConditionHandler extends SingletonFactory
{
    /**
     * list of available conditions grouped by the id of the related condition
     * object type definition
     * @var array<int, array<int, mixed[]>>
     */
    protected $conditions = [];

    /**
     * Creates condition objects for the object with the given id and based
     * on the given condition object types.
     *
     * @param int $objectID
     * @param ObjectType[] $conditionObjectTypes
     * @return void
     */
    public function createConditions($objectID, array $conditionObjectTypes)
    {
        foreach ($conditionObjectTypes as $objectType) {
            $conditionData = $objectType->getProcessor()->getData();
            if ($conditionData !== null) {
                $conditionAction = new ConditionAction([], 'create', [
                    'data' => [
                        'conditionData' => \serialize($conditionData),
                        'objectID' => $objectID,
                        'objectTypeID' => $objectType->objectTypeID,
                    ],
                ]);
                $conditionAction->executeAction();
            }
        }
    }

    /**
     * Deletes all conditions of the objects with the given ids.
     *
     * @param string $definitionName
     * @param int[] $objectIDs
     * @return void
     * @throws SystemException
     */
    public function deleteConditions($definitionName, array $objectIDs)
    {
        if (empty($objectIDs)) {
            return;
        }

        $definition = ObjectTypeCache::getInstance()->getDefinitionByName($definitionName);
        if ($definition === null) {
            throw new SystemException("Unknown object type definition with name '" . $definitionName . "'");
        }

        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes($definitionName);
        $objectTypeIDs = [];
        foreach ($objectTypes as $objectType) {
            $objectTypeIDs[] = $objectType->objectTypeID;
        }

        if (empty($objectTypeIDs)) {
            return;
        }

        $conditionList = new ConditionList();
        $conditionList->getConditionBuilder()->add('objectTypeID IN (?)', [$objectTypeIDs]);
        $conditionList->getConditionBuilder()->add('objectID IN (?)', [$objectIDs]);
        $conditionList->readObjects();

        if (\count($conditionList)) {
            $conditionAction = new ConditionAction($conditionList->getObjects(), 'delete');
            $conditionAction->executeAction();
        }
    }

    /**
     * Returns the conditions for the conditioned object with the given condition
     * object type definition and object id.
     *
     * @param string $definitionName
     * @param int $objectID
     * @return Condition[]
     * @throws SystemException
     */
    public function getConditions($definitionName, $objectID)
    {
        // validate definition
        $definition = ObjectTypeCache::getInstance()->getDefinitionByName($definitionName);
        if ($definition === null) {
            throw new SystemException("Unknown object type definition with name '" . $definitionName . "'");
        }

        if (!isset($this->conditions[$definition->definitionID])) {
            $this->conditions[$definition->definitionID] = ConditionCacheBuilder::getInstance()->getData([
                'definitionID' => $definition->definitionID,
            ]);
        }

        if (isset($this->conditions[$definition->definitionID][$objectID])) {
            return $this->conditions[$definition->definitionID][$objectID];
        }

        return [];
    }

    /**
     * Updates the conditions for the object with the given object id.
     *
     * @param int $objectID
     * @param Condition[] $oldConditions
     * @param ObjectType[] $conditionObjectTypes
     * @return void
     */
    public function updateConditions($objectID, array $oldConditions, array $conditionObjectTypes)
    {
        // delete old conditions first
        $conditionAction = new ConditionAction($oldConditions, 'delete');
        $conditionAction->executeAction();

        // create new conditions
        $this->createConditions($objectID, $conditionObjectTypes);
    }

    /**
     * Returns the list of conditions with assigned filter for the condition provider and stored condition-values.
     *
     * @template T of IConditionType
     * @param AbstractConditionProvider<T> $provider
     * @param array{identifier: string, value: ConditionValue}[] $conditions
     *
     * @return T[]
     */
    public function getConditionsWithFilter(AbstractConditionProvider $provider, array $conditions): array
    {
        $result = [];
        foreach ($conditions as $condition) {
            $_conditionType = $provider->getConditionByIdentifier($condition['identifier']);
            if ($_conditionType === null) {
                if (ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS) {
                    throw new \InvalidArgumentException("Condition type with identifier '{$condition['identifier']}' not found.");
                }

                continue;
            }

            $conditionType = clone $_conditionType;
            $conditionType->setFilter($condition['value']);

            $result[] = $conditionType;
        }

        return $result;
    }
}
