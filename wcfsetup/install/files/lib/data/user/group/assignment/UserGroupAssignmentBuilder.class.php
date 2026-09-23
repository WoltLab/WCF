<?php

namespace wcf\data\user\group\assignment;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\cache\builder\UserGroupAssignmentCacheBuilder;
use wcf\system\condition\ConditionHandler;

/**
 * Builder for creating, updating and deleting automatic user group assignments.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserGroupAssignment>
 */
final class UserGroupAssignmentBuilder extends DatabaseObjectBuilder
{
    public function setTitle(string $title): static
    {
        $this->properties['title'] = $title;

        return $this;
    }

    /**
     * Sets the user group to which the users are automatically assigned.
     */
    public function setGroupID(int $groupID): static
    {
        $this->properties['groupID'] = $groupID;

        return $this;
    }

    public function setIsDisabled(bool $isDisabled): static
    {
        $this->properties['isDisabled'] = (int)$isDisabled;

        return $this;
    }

    /**
     * Sets the serialized object filters that a user must match.
     */
    public function setConditions(?string $conditions): static
    {
        $this->properties['conditions'] = $conditions;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['groupID', 'title'];
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        ConditionHandler::getInstance()->deleteConditions(
            'com.woltlab.wcf.condition.userGroupAssignment',
            $objectIDs
        );
    }

    public static function resetCache(): void
    {
        UserGroupAssignmentCacheBuilder::getInstance()->reset();
        ConditionCacheBuilder::getInstance()->reset([
            'definitionID' => ObjectTypeCache::getInstance()
                ->getDefinitionByName('com.woltlab.wcf.condition.userGroupAssignment')
                ->definitionID,
        ]);
    }
}
