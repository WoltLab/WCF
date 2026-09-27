<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\WCF;

/**
 * Filters users whose email address contains the given text, ignoring case.
 * Guests never match.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @implements IObjectListFilter<User, string>
 */
final class UserEmailObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userEmail';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.user.email');
    }

    #[\Override]
    public function getFormField(): TextFormField
    {
        return TextFormField::create('userEmail')
            ->label('wcf.user.email')
            ->maximumLength(255)
            ->required();
    }

    #[\Override]
    public function isAvailable(): bool
    {
        return true;
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return $value;
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): string
    {
        return $serializedValue;
    }

    #[\Override]
    public function toFormFieldValue(mixed $value): string
    {
        return $value;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.user.email.summary', [
            'value' => $value,
        ]);
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        $conditions->add('email LIKE ?', ['%' . WCF::getDB()->escapeLikeValue($value) . '%']);
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        if ($object->isGuest()) {
            return false;
        }

        // Must match the case-insensitive `LIKE` in `applyFilter()`.
        return \mb_stripos($object->email, $configuredValue) !== false;
    }
}
