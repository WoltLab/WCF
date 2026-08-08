<?php

namespace wcf\system\importer;

use wcf\data\user\group\UserGroup;
use wcf\data\user\rank\UserRank;
use wcf\data\user\rank\UserRankEditor;

/**
 * Imports user ranks.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserRankImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = UserRank::class;

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        $data['groupID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user.group', $data['groupID']);
        if ($data['groupID'] === null) {
            $data['groupID'] = UserGroup::getGroupByType(UserGroup::USERS)->groupID;
        }

        $rank = UserRankEditor::create($data);

        ImportHandler::getInstance()->saveNewID('com.woltlab.wcf.user.rank', $oldID, $rank->rankID);

        return $rank->rankID;
    }
}
