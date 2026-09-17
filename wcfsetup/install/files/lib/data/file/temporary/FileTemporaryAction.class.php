<?php

namespace wcf\data\file\temporary;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @deprecated 6.3 Use `FileTemporaryBuilder` instead.
 *
 * @extends AbstractDatabaseObjectAction<FileTemporary, FileTemporaryEditor>
 */
class FileTemporaryAction extends AbstractDatabaseObjectAction
{
    protected $className = FileTemporaryEditor::class;
}
