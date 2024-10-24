<?php

namespace wcf\data\user\option\category;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes user option category-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserOptionCategory      create()
 * @method  UserOptionCategoryEditor[]  getObjects()
 * @method  UserOptionCategoryEditor    getSingleObject()
 */
class UserOptionCategoryAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = UserOptionCategoryEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.user.canManageUserOption'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.user.canManageUserOption'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.user.canManageUserOption'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'update'];

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        parent::validateDelete();

        $categoryNames = [];
        foreach ($this->getObjects() as $categoryEditor) {
            $categoryNames[] = $categoryEditor->categoryName;
        }

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("categoryName IN (?)", [$categoryNames]);
        $sql = "SELECT  COUNT(*) AS count
                FROM    wcf1_user_option
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());
        $count = $statement->fetchSingleColumn();
        if ($count > 0) {
            throw new UserInputException('objectIDs');
        }
    }
}
