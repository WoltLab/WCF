<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\language\LanguageFactory;
use wcf\system\object\filter\AbstractMultipleSelectionObjectFilter;
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
 * @extends AbstractMultipleSelectionObjectFilter<User>
 * @implements IObjectListFilter<User, list<int>>
 */
final class UserLanguageObjectFilter extends AbstractMultipleSelectionObjectFilter implements IObjectListFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.userLanguage';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.user.language';
    }

    #[\Override]
    protected function getLabels(array $objectIDs): array
    {
        $labels = [];
        foreach ($objectIDs as $languageID) {
            $language = LanguageFactory::getInstance()->getLanguage($languageID);
            if ($language !== null) {
                $labels[] = $language->__toString();
            }
        }

        return $labels;
    }

    #[\Override]
    public function getFormField(): MultipleSelectionFormField
    {
        return MultipleSelectionFormField::create('userLanguage')
            ->label('wcf.user.condition.languages')
            ->options(LanguageFactory::getInstance()->getLanguages())
            ->required();
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
