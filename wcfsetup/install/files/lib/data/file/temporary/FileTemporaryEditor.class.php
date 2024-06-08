<?php

namespace wcf\data\file\temporary;

use wcf\data\DatabaseObjectEditor;

/**
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 *
 * @method static FileTemporary create(array $parameters = [])
 * @method FileTemporary getDecoratedObject()
 * @mixin FileTemporary
 */
class FileTemporaryEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = FileTemporary::class;
}
