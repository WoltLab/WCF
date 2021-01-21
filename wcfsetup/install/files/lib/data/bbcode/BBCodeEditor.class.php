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
 * @package WoltLabSuite\Core\Data\Bbcode
 *
 * @method static   BBCode      create(array $parameters = [])
 * @method      BBCode      getDecoratedObject()
 * @mixin       BBCode
 */
class BBCodeEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    public static $baseClass = BBCode::class;

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        BBCodeCacheBuilder::getInstance()->reset();
    }
}
