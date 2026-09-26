<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\object\filter\AbstractBooleanObjectFilter;
use wcf\util\UserUtil;

/**
 * Filters by whether the active request originates from a mobile browser.
 *
 * The given user is ignored, therefore this filter is only meaningful when
 * testing the active user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractBooleanObjectFilter<User>
 */
final class UserMobileBrowserObjectFilter extends AbstractBooleanObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userMobileBrowser';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.user.mobileBrowser';
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        return $configuredValue === UserUtil::usesMobileBrowser();
    }
}
