<?php

namespace wcf\data\user\object\watch;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit watched objects.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `UserObjectWatchBuilder` instead.
 *
 * @mixin       UserObjectWatch
 * @extends DatabaseObjectEditor<UserObjectWatch>
 */
class UserObjectWatchEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserObjectWatch::class;
}
