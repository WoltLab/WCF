<?php

namespace wcf\data\file;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method File create()
 * @method FileEditor[] getObjects()
 * @method FileEditor getSingleObject()
 */
class FileAction extends AbstractDatabaseObjectAction
{
    protected $className = FileEditor::class;
}
