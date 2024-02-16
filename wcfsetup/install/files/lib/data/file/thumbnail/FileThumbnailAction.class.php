<?php

namespace wcf\data\file\thumbnail;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @method FileThumbnail create()
 * @method FileThumbnailEditor[] getObjects()
 * @method FileThumbnailEditor getSingleObject()
 */
class FileThumbnailAction extends AbstractDatabaseObjectAction
{
    protected $className = FileThumbnailEditor::class;
}
