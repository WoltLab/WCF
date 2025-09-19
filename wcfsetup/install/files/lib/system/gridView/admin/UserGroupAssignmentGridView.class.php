<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserGroupAssignmentEditForm;
use wcf\data\DatabaseObject;
use wcf\data\user\group\assignment\UserGroupAssignment;
use wcf\data\user\group\assignment\UserGroupAssignmentList;
use wcf\data\user\group\UserGroup;
use wcf\event\gridView\admin\UserGroupAssignmentGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\SelectFilter;
use wcf\system\gridView\filter\TextFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\UserGroupAssignmentInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of user group assignments.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<UserGroupAssignment, UserGroupAssignmentList>
 */
final class UserGroupAssignmentGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for("assignmentID")
                ->label("wcf.global.objectID")
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for("title")
                ->label("wcf.global.name")
                ->titleColumn()
                ->filter(new TextFilter())
                ->sortable(),
            GridViewColumn::for("groupID")
                ->label("wcf.acp.group.assignment.userGroup")
                ->filter(
                    new SelectFilter(UserGroup::getSortedGroupsByType([], [
                        UserGroup::EVERYONE,
                        UserGroup::GUESTS,
                        UserGroup::OWNER,
                        UserGroup::USERS,
                    ]))
                )
                ->sortable()
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof UserGroupAssignment);

                            return StringUtil::encodeHTML($row->getUserGroup()->getTitle());
                        }
                    }
                )
        ]);

        $provider = new UserGroupAssignmentInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(UserGroupAssignmentEditForm::class)
        ]);
        $this->setInteractionProvider($provider);
        $this->addQuickInteraction(
            new ToggleInteraction(
                "enabled",
                "core/users/groups/assignments/%s/enable",
                "core/users/groups/assignments/%s/disable"
            )
        );

        $this->setDefaultSortField("title");
        $this->addRowLink(new GridViewRowLink(UserGroupAssignmentEditForm::class));
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.user.canManageGroupAssignment');
    }

    #[\Override]
    protected function createObjectList(): UserGroupAssignmentList
    {
        return new UserGroupAssignmentList();
    }

    #[\Override]
    protected function getInitializedEvent(): UserGroupAssignmentGridViewInitialized
    {
        return new UserGroupAssignmentGridViewInitialized($this);
    }
}
