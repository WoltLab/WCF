<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\WCF;

/**
 * Filters users that have not received the trophy with the given id.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @implements IObjectListFilter<User, int>
 */
final class UserNoTrophyObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userNoTrophy';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.user.noTrophy');
    }

    #[\Override]
    public function getFormField(): SelectFormField
    {
        return SelectFormField::create('userNoTrophy')
            ->label('wcf.objectFilter.user.trophy.trophy')
            ->options(UserTrophyObjectFilter::getSelectableTrophies(), labelLanguageItems: false)
            ->required();
    }

    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return (string)$value;
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): int
    {
        return (int)$serializedValue;
    }

    #[\Override]
    public function toFormFieldValue(mixed $value): int
    {
        return $value;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.user.noTrophy.summary', [
            'trophy' => TrophyCache::getInstance()->getTrophyByID($value)?->getTitle() ?? $value,
        ]);
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        $conditions->add(
            'user_table.userID NOT IN (
                SELECT  userID
                FROM    wcf1_user_trophy
                WHERE   trophyID = ?
            )',
            [$value]
        );
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        return !\in_array($configuredValue, UserTrophyObjectFilter::getTrophyIDs($object), true);
    }
}
