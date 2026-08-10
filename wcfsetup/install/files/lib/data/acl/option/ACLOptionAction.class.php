<?php

namespace wcf\data\acl\option;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\acl\ACLHandler;

/**
 * Executes acl option-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<ACLOption, ACLOptionEditor>
 */
class ACLOptionAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = ACLOptionEditor::class;

    /**
     * @inheritDoc
     */
    protected $requireACP = ['loadAll'];

    /**
     * Validates parameters for ACL options.
     *
     * @return void
     */
    public function validateLoadAll()
    {
        $this->readInteger('objectID', true);
        $this->readInteger('objectTypeID');
        $this->readString('categoryName', true);
    }

    /**
     * Returns a set of permissions and their values if applicable.
     *
     * @return mixed[]
     */
    public function loadAll()
    {
        $objectIDs = $this->parameters['objectID'] !== 0 ? [$this->parameters['objectID']] : [];

        return ACLHandler::getInstance()->getPermissions(
            $this->parameters['objectTypeID'],
            $objectIDs,
            $this->parameters['categoryName'],
            true
        );
    }
}
