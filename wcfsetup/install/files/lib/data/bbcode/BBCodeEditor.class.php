<?php

namespace wcf\data\bbcode;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\BBCodeCacheBuilder;

/**
 * Provides functions to edit bbcodes.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       BBCode
 * @extends DatabaseObjectEditor<BBCode>
 * @implements IEditableCachedObject<BBCode>
 */
class BBCodeEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    public static $baseClass = BBCode::class;

    #[\Override]
    public static function resetCache()
    {
        BBCodeCacheBuilder::getInstance()->reset();
    }
}
