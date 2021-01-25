<?php

namespace wcf\data\acp\session;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP session-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Acp\Session
 *
 * @method  ACPSession      create()
 * @method  ACPSessionEditor[]  getObjects()
 * @method  ACPSessionEditor    getSingleObject()
 * @deprecated  5.4 Distinct ACP sessions have been removed. This class is preserved due to its use in legacy sessions.
 */
class ACPSessionAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = ACPSessionEditor::class;
}
