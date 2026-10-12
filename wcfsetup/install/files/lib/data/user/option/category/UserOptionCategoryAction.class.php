<?php

namespace wcf\data\user\option\category;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user option category-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<UserOptionCategory, UserOptionCategoryEditor>
 * @deprecated 6.3 use `UserOptionCategoryBuilder` instead.
 */
class UserOptionCategoryAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = UserOptionCategoryEditor::class;
}
