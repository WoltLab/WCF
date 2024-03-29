<?php

namespace wcf\system\cache\builder;

use wcf\data\user\option\UserOption;

/**
 * Caches user options and categories
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserOptionCacheBuilder extends OptionCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $optionClassName = UserOption::class;

    /**
     * @inheritDoc
     */
    protected $tableName = 'user_option';
}
