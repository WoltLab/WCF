<?php

namespace wcf\data\application;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\eager\ApplicationCache;

/**
 * Provides functions to edit applications.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       Application
 * @extends DatabaseObjectEditor<Application>
 * @implements IEditableCachedObject<Application>
 */
class ApplicationEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Application::class;

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        (new ApplicationCache())->rebuild();
    }
}
