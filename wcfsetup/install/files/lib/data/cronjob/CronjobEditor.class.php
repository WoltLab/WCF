<?php

namespace wcf\data\cronjob;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\CronjobCacheBuilder;

/**
 * Provides functions to edit cronjobs.
 *
 * The localized description is written by `CronjobBuilder`.
 *
 * @author  Alexander Ebert, Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   Cronjob
 * @extends DatabaseObjectEditor<Cronjob>
 * @implements IEditableCachedObject<Cronjob>
 */
class CronjobEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Cronjob::class;

    #[\Override]
    public static function resetCache()
    {
        CronjobCacheBuilder::getInstance()->reset();
    }
}
