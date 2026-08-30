<?php

namespace wcf\data\acp\session\log;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP session log-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `ACPSessionLogBuilder` instead.
 *
 * @extends AbstractDatabaseObjectAction<ACPSessionLog, ACPSessionLogEditor>
 */
class ACPSessionLogAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = ACPSessionLogEditor::class;
}
