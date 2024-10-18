<?php

namespace wcf\data\bbcode;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Executes bbcode-related actions.
 *
 * @author  Tim Duesterhus, Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  BBCodeEditor[]  getObjects()
 * @method  BBCodeEditor    getSingleObject()
 */
class BBCodeAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = BBCodeEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.content.bbcode.canManageBBCode'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.content.bbcode.canManageBBCode'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['delete', 'update'];

    /**
     * @inheritDoc
     * @return  BBCode
     */
    public function create()
    {
        /** @var BBCode $bbCode */
        $bbCode = parent::create();

        // add bbcode to BBCodeSelect user group options
        $sql = "SELECT  optionID
                FROM    wcf1_user_group_option
                WHERE   optionType = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute(['BBCodeSelect']);
        $optionIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (!empty($optionIDs)) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add("optionID IN (?)", [$optionIDs]);
            $conditionBuilder->add("groupID IN (?)", [UserGroup::getGroupIDsByType([UserGroup::EVERYONE])]);
            $conditionBuilder->add("optionValue <> ?", ['all']);

            $sql = "SELECT  *
                    FROM    wcf1_user_group_option_value
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($conditionBuilder->getParameters());

            $sql = "UPDATE  wcf1_user_group_option_value
                    SET     optionValue = ?
                    WHERE   optionID = ?
                        AND groupID = ?";
            $updateStatement = WCF::getDB()->prepare($sql);

            WCF::getDB()->beginTransaction();
            while ($row = $statement->fetchArray()) {
                if (!empty($row['optionValue'])) {
                    $row['optionValue'] .= ',' . $bbCode->bbcodeTag;
                } else {
                    $row['optionValue'] = $bbCode->bbcodeTag;
                }

                $updateStatement->execute([
                    $row['optionValue'],
                    $row['optionID'],
                    $row['groupID'],
                ]);
            }
            WCF::getDB()->commitTransaction();

            // clear user group option cache
            UserGroupEditor::resetCache();
        }

        return $bbCode;
    }

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        parent::validateDelete();

        foreach ($this->getObjects() as $bbcode) {
            if (!$bbcode->canDelete()) {
                throw new PermissionDeniedException();
            }
        }
    }
}
