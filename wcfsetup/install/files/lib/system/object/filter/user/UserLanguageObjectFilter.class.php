<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\language\LanguageFactory;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\WCF;

/**
 * Filters users by their interface language. The value is the list of ids of
 * the accepted languages, users must use one of them.
 *
 * Guests are matched by the language of the active session.
 *
 * @author      Alexander Ebert, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @implements IObjectListFilter<User, list<int>>
 */
final class UserLanguageObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userLanguage';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.user.language');
    }

    #[\Override]
    public function getFormField(): MultipleSelectionFormField
    {
        return MultipleSelectionFormField::create('userLanguage')
            ->label('wcf.user.condition.languages')
            ->options(LanguageFactory::getInstance()->getLanguages())
            ->required();
    }

    /**
     * @param list<int|string> $value
     */
    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return \implode(',', \array_map(static fn($languageID) => (int)$languageID, $value));
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): array
    {
        return \array_map(static fn($languageID) => (int)$languageID, \explode(',', $serializedValue));
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        $languages = [];
        foreach ($value as $languageID) {
            $language = LanguageFactory::getInstance()->getLanguage($languageID);
            if ($language !== null) {
                $languages[] = $language->__toString();
            }
        }

        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.user.language.summary', [
            'languages' => \implode(', ', $languages),
        ]);
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        $conditions->add('languageID IN (?)', [$value]);
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        if ($object->isGuest()) {
            return \in_array(WCF::getLanguage()->languageID, $configuredValue, true);
        }

        return \in_array($object->languageID, $configuredValue, true);
    }
}
