<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\object\filter\IObjectFilter;
use wcf\system\WCF;
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
 * @implements IObjectFilter<User, bool>
 */
final class UserMobileBrowserObjectFilter implements IObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userMobileBrowser';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.user.mobileBrowser');
    }

    #[\Override]
    public function getFormField(): BooleanFormField
    {
        return BooleanFormField::create('userMobileBrowser')
            ->label('wcf.objectFilter.user.mobileBrowser');
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return $value ? '1' : '0';
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): bool
    {
        return (bool)$serializedValue;
    }

    #[\Override]
    public function toFormFieldValue(mixed $value): bool
    {
        return $value;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        if ($value) {
            return WCF::getLanguage()->get('wcf.objectFilter.user.mobileBrowser.summary.yes');
        }

        return WCF::getLanguage()->get('wcf.objectFilter.user.mobileBrowser.summary.no');
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        return $configuredValue === UserUtil::usesMobileBrowser();
    }
}
