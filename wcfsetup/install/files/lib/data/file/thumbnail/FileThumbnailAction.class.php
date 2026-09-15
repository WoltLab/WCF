<?php

namespace wcf\data\file\thumbnail;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @deprecated 6.3 Use `FileThumbnailBuilder` instead.
 *
 * @extends AbstractDatabaseObjectAction<FileThumbnail, FileThumbnailEditor>
 */
class FileThumbnailAction extends AbstractDatabaseObjectAction
{
    protected $className = FileThumbnailEditor::class;
}
