<?php

namespace wcf\data\file;

use wcf\data\DatabaseObjectEditor;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @method static File create(array $parameters = [])
 * @method File getDecoratedObject()
 * @mixin File
 */
class FileEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = File::class;
}
