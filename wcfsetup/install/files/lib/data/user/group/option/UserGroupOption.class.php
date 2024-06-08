<?php

namespace wcf\data\user\group\option;

use wcf\data\option\Option;

/**
 * Represents a user group option.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   string $defaultValue       default value of the user option
 * @property-read   int $usersOnly          is `1` if the option only applies to user groups for registered users, otherwise `1`
 */
class UserGroupOption extends Option
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'user_group_option';

    /**
     * @since 6.1
     */
    public function addAdditionalData(string $key, mixed $value): void
    {
        $this->data['additionalData'][$key] = $value;
    }

    /**
     * List of permission names that may not be altered when the enterprise mode is active.
     * @var string[]
     */
    const ENTERPRISE_BLACKLIST = [
        // Configuration
        'admin.configuration.package.canUpdatePackage',
        'admin.configuration.package.canEditServer',

        // User
        'admin.user.canMailUser',

        // Management
        'admin.management.canImportData',
        'admin.management.canManageCronjob',
        'admin.management.canRebuildData',
    ];
}
