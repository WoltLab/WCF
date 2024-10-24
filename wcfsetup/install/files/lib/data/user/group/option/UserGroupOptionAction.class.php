<?php

namespace wcf\data\user\group\option;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\user\group\UserGroupEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Executes user group option-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserGroupOption         create()
 * @method  UserGroupOptionEditor[]     getObjects()
 * @method  UserGroupOptionEditor       getSingleObject()
 */
class UserGroupOptionAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = UserGroupOptionEditor::class;

    /**
     * Updates option values for given option id.
     */
    public function updateValues()
    {
        /** @var UserGroupOption $option */
        $option = \current($this->objects);

        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("optionID = ?", [$option->optionID]);
        if (!empty($this->parameters['values'])) {
            $groupIDs = \array_keys($this->parameters['values']);
            $conditions->add("groupID IN (?)", [$groupIDs]);
        }

        // remove old values
        $sql = "DELETE FROM wcf1_user_group_option_value
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditions->getParameters());

        if (!empty($this->parameters['values'])) {
            $sql = "INSERT INTO wcf1_user_group_option_value
                                (optionID, groupID, optionValue)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);

            WCF::getDB()->beginTransaction();
            foreach ($this->parameters['values'] as $groupID => $optionValue) {
                $statement->execute([
                    $option->optionID,
                    $groupID,
                    $optionValue,
                ]);
            }
            WCF::getDB()->commitTransaction();
        }

        // clear cache
        UserGroupEditor::resetCache();
    }
}
