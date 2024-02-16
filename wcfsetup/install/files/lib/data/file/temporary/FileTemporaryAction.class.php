<?php

namespace wcf\data\file\temporary;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @method FileTemporary create()
 * @method FileTemporaryEditor[] getObjects()
 * @method FileTemporaryEditor getSingleObject()
 */
class FileTemporaryAction extends AbstractDatabaseObjectAction
{
    protected $className = FileTemporaryEditor::class;
}
