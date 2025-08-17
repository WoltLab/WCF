<?php

namespace wcf\data\user\group\option;

use wcf\command\user\group\option\UpdateUserGroupOptionValues;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user group option-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<UserGroupOption, UserGroupOptionEditor>
 */
class UserGroupOptionAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = UserGroupOptionEditor::class;

    /**
     * Updates option values for given option id.
     *
     * @return void
     *
     * @deprecated 6.3 use the `UpdateUserGroupOptionValues` command instead.
     */
    public function updateValues()
    {
        $editor = $this->getSingleObject();

        (new UpdateUserGroupOptionValues($editor->getDecoratedObject(), $this->parameters['values'] ?? []))();
    }
}
