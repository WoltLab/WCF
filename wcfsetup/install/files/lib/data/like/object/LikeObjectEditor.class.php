<?php

namespace wcf\data\like\object;

use wcf\data\DatabaseObjectEditor;

/**
 * Extends the LikeObject object with functions to create, update and delete liked objects.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin LikeObject
 * @extends DatabaseObjectEditor<LikeObject>
 * @deprecated 6.3 use `LikeObjectBuilder` instead.
 */
class LikeObjectEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = LikeObject::class;
}
