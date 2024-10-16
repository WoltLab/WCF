<?php

namespace wcf\data\spider;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes spider-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Spider      create()
 * @method  SpiderEditor[]  getObjects()
 * @method  SpiderEditor    getSingleObject()
 */
class SpiderAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = SpiderEditor::class;
}
