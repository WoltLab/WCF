<?php

namespace wcf\system\box\command;

use wcf\data\box\Box;
use wcf\data\condition\ConditionAction;
use wcf\system\WCF;

/**
 * Creates a new condition for an existing box.
 *
 * Note: The primary use of this command is to be used during package installation.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CreateBoxCondition
{
    public function __construct(
        private readonly string $boxIdentifier,
        private readonly string $conditionDefinition,
        private readonly string $conditionObjectType,
        private readonly array $conditionData
    ) {
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function __invoke()
    {
        $objectTypeID = $this->getObjectTypeID();
        if (!$objectTypeID) {
            throw new \InvalidArgumentException(
                "Unknown box condition '{$this->conditionObjectType}' of condition definition '{$this->conditionDefinition}'"
            );
        }

        $box = Box::getBoxByIdentifier($this->boxIdentifier);
        if ($box === null) {
            throw new \InvalidArgumentException("Unknown box with identifier '{$this->boxIdentifier}'");
        }

        (new ConditionAction([], 'create', [
            'data' => [
                'conditionData' => \serialize($this->conditionData),
                'objectID' => $box->boxID,
                'objectTypeID' => $objectTypeID,
            ],
        ]))->executeAction();
    }

    private function getObjectTypeID(): ?int
    {
        // do not rely on caches during package installation
        $sql = "SELECT      objectTypeID
                FROM        wcf1_object_type object_type
                INNER JOIN  wcf1_object_type_definition object_type_definition
                ON          object_type.definitionID = object_type_definition.definitionID
                WHERE       objectType = ?
                        AND definitionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->conditionObjectType, $this->conditionDefinition]);

        return $statement->fetchSingleColumn();
    }
}
