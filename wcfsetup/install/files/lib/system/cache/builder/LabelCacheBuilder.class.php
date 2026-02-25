<?php

namespace wcf\system\cache\builder;

use wcf\data\acl\option\ACLOption;
use wcf\data\label\group\LabelGroupList;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\label\LabelList;
use wcf\system\acl\ACLHandler;

/**
 * Caches labels and groups.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @phpstan-type LabelCache array{
 *  options: array<int, ACLOption>,
 *  groups: array<int, ViewableLabelGroup>,
 * }
 */
class LabelCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $data = [
            'options' => [],
            'groups' => [],
        ];

        // get label groups
        $groupList = new LabelGroupList();
        $groupList->decoratorClassName = ViewableLabelGroup::class;
        $groupList->readObjects();
        $data['groups'] = $groupList->getObjects();

        // get permissions for groups
        $permissions = ACLHandler::getInstance()->getPermissions(
            ACLHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.label'),
            \array_keys($data['groups'])
        );

        // store options
        $data['options'] = $permissions['options']->getObjects();

        // assign permissions for each label group
        foreach ($data['groups'] as $groupID => $group) {
            // @phpstan-ignore function.impossibleType, instanceof.alwaysFalse
            \assert($group instanceof ViewableLabelGroup);

            // group permissions
            if (isset($permissions['group'][$groupID])) {
                $group->setGroupPermissions($permissions['group'][$groupID]);
            }

            // user permissions
            if (isset($permissions['user'][$groupID])) {
                $group->setUserPermissions($permissions['user'][$groupID]);
            }
        }

        if (\count($groupList)) {
            // get labels
            $labelList = new LabelList();
            $labelList->readObjects();
            foreach ($labelList as $label) {
                /** @var ViewableLabelGroup $labelGroup */
                $labelGroup = $data['groups'][$label->groupID];
                $labelGroup->addLabel($label);
            }
        }

        return $data;
    }
}
