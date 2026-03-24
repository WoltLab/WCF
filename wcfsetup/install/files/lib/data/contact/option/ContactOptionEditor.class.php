<?php

namespace wcf\data\contact\option;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\ContactOptionCacheBuilder;

/**
 * Provides functions to edit contact recipients.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   ContactOption
 * @extends DatabaseObjectEditor<ContactOption>
 * @implements IEditableCachedObject<ContactOption>
 */
class ContactOptionEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ContactOption::class;

    #[\Override]
    public static function resetCache()
    {
        ContactOptionCacheBuilder::getInstance()->reset();
    }
}
