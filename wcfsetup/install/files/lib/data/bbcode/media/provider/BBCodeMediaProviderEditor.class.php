<?php

namespace wcf\data\bbcode\media\provider;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\BBCodeMediaProviderCacheBuilder;

/**
 * Provides functions to edit BBCode media providers.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static BBCodeMediaProvider create(array $parameters = [])
 * @method      BBCodeMediaProvider getDecoratedObject()
 * @mixin       BBCodeMediaProvider
 */
class BBCodeMediaProviderEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    public static $baseClass = BBCodeMediaProvider::class;

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        BBCodeMediaProviderCacheBuilder::getInstance()->reset();
    }
}
