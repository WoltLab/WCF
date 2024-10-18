<?php

namespace wcf\system\cache\builder;

use wcf\data\user\group\option\UserGroupOption;
use wcf\data\user\group\UserGroup;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\ClassNotFoundException;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\option\user\group\IUserGroupOptionType;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Caches the merged user group options for a certain user group combination.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserGroupPermissionCacheBuilder extends AbstractCacheBuilder
{
    /**
     * list of used group option type objects
     * @var IUserGroupOptionType[]
     */
    protected $typeObjects = [];

    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $data = $excludedInTinyBuild = [];

        if (VISITOR_USE_TINY_BUILD) {
            foreach ($parameters as $groupID) {
                if (UserGroup::getGroupByID($groupID)->groupType == UserGroup::GUESTS) {
                    $sql = "SELECT  optionName, additionalData
                            FROM    wcf1_user_group_option
                            WHERE   optionType = 'boolean'";
                    $statement = WCF::getDB()->prepare($sql);
                    $statement->execute();
                    while ($option = $statement->fetchObject(UserGroupOption::class)) {
                        if ($option->excludedInTinyBuild) {
                            $excludedInTinyBuild[] = $option->optionName;
                        }
                    }

                    break;
                }
            }
        }

        // get option values
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("option_value.groupID IN (?)", [$parameters]);

        $optionData = [];
        $sql = "SELECT      option_table.optionName,
                            option_table.optionType,
                            option_value.optionValue,
                            option_value.groupID,
                            option_table.enableOptions,
                            option_table.usersOnly
                FROM        wcf1_user_group_option_value option_value
                LEFT JOIN   wcf1_user_group_option option_table
                ON          option_table.optionID = option_value.optionID
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        while ($row = $statement->fetchArray()) {
            if (
                $row['usersOnly']
                && UserGroup::getGroupByID($row['groupID'])->groupType == UserGroup::GUESTS
            ) {
                continue;
            }

            $optionData[$row['groupID']][$row['optionName']] = $row;
        }

        foreach ($optionData as $options) {
            $optionBlacklist = [];

            foreach ($options as $option) {
                if ($option['enableOptions']) {
                    $typeObj = $this->getTypeObject($option['optionType']);
                    $disabledOptions = $typeObj->getDisabledOptionNames(
                        $option['optionValue'],
                        $option['enableOptions']
                    );
                    if (!empty($disabledOptions)) {
                        $optionBlacklist = \array_merge($optionBlacklist, $disabledOptions);
                    }
                }
            }

            $options = \array_filter($options, static function ($optionName) use (&$optionBlacklist) {
                return !\in_array($optionName, $optionBlacklist);
            }, \ARRAY_FILTER_USE_KEY);

            foreach ($options as $option) {
                if (!isset($data[$option['optionName']])) {
                    $data[$option['optionName']] = ['type' => $option['optionType'], 'values' => []];
                }

                $data[$option['optionName']]['values'][] = $option['optionValue'];
            }
        }

        $includesOwnerGroup = false;
        $ownerGroup = UserGroup::getGroupByType(UserGroup::OWNER);
        if ($ownerGroup && \in_array($ownerGroup->groupID, $parameters)) {
            $includesOwnerGroup = true;
        }

        $forceGrantPermission = [];
        if ($includesOwnerGroup) {
            $forceGrantPermission = UserGroup::getOwnerPermissions();
        }

        // merge values
        $neverValues = [];
        foreach ($data as $optionName => $option) {
            if (\in_array($optionName, $excludedInTinyBuild)) {
                // mimic the behavior of 'Never', regardless of what is actually set
                $result = -1;
            } elseif (\count($option['values']) == 1) {
                $result = $option['values'][0];
            } else {
                $typeObj = $this->getTypeObject($option['type']);
                $result = \array_shift($option['values']);
                foreach ($option['values'] as $value) {
                    $newValue = $typeObj->merge($result, $value);
                    if ($newValue !== null) {
                        $result = $newValue;
                    }
                }
            }

            if ($ownerGroup && $optionName === 'admin.user.accessibleGroups') {
                $accessibleGroupIDs = \explode(',', $result);
                if ($includesOwnerGroup) {
                    // Regardless of the actual permissions, the owner group has access to all groups.
                    $accessibleGroupIDs = \array_keys(UserGroup::getAllGroups());
                } elseif (!$includesOwnerGroup && \in_array($ownerGroup->groupID, $accessibleGroupIDs)) {
                    $accessibleGroupIDs = \array_diff($accessibleGroupIDs, [$ownerGroup->groupID]);
                }

                $result = \implode(',', $accessibleGroupIDs);
            } elseif ($includesOwnerGroup && \in_array($optionName, $forceGrantPermission)) {
                $result = 1;
            }

            // handle special value 'Never' for boolean options
            if ($option['type'] === 'boolean' && $result == -1) {
                $neverValues[$optionName] = $optionName;
                $result = 0;
            }

            // unset false values
            if ($result === false) {
                unset($data[$optionName]);
            } else {
                $data[$optionName] = $result;
            }
        }

        $data['__never'] = $neverValues;
        $data['groupIDs'] = $parameters;

        return $data;
    }

    /**
     * Returns an object of the requested group option type.
     *
     * @param string $type
     * @return  IUserGroupOptionType
     * @throws  SystemException
     */
    protected function getTypeObject($type)
    {
        if (!isset($this->typeObjects[$type])) {
            $className = 'wcf\system\option\user\group\\' . StringUtil::firstCharToUpperCase($type) . 'UserGroupOptionType';

            // validate class
            if (!\class_exists($className)) {
                throw new ClassNotFoundException($className);
            }
            if (!\is_subclass_of($className, IUserGroupOptionType::class)) {
                throw new ImplementationException($className, IUserGroupOptionType::class);
            }

            // create instance
            $this->typeObjects[$type] = new $className();
        }

        return $this->typeObjects[$type];
    }
}
