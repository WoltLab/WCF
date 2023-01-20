<?php

namespace wcf\data\object\type;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes object type-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  ObjectType      create()
 * @method  ObjectTypeEditor[]  getObjects()
 * @method  ObjectTypeEditor    getSingleObject()
 */
class ObjectTypeAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = ObjectTypeEditor::class;
}
