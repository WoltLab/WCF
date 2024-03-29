<?php

namespace wcf\data\user\object\watch;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit watched objects.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static UserObjectWatch     create(array $parameters = [])
 * @method      UserObjectWatch     getDecoratedObject()
 * @mixin       UserObjectWatch
 */
class UserObjectWatchEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserObjectWatch::class;
}
