<?php

namespace wcf\system\object\filter\user;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\form\builder\field\NumericRangeFormField;
use wcf\system\object\filter\AbstractRangeObjectFilter;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\WCF;

/**
 * Filters users by the value of an integer column of the user table, e.g.
 * `activityPoints`. The value consists of an optional minimum and an optional
 * maximum, both inclusive. Guests never match.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractRangeObjectFilter<User, int>
 * @implements IObjectListFilter<User, array{0: ?int, 1: ?int}>
 */
final class UserIntegerPropertyObjectFilter extends AbstractRangeObjectFilter implements IObjectListFilter
{
    /**
     * @param string $propertyName name of the integer column in `wcf1_user`
     * @param string $languageItem language item of the title of the property
     * @param array<string, mixed> $languageItemVariables variables of the language item of the title
     */
    public function __construct(
        private readonly string $propertyName,
        private readonly string $languageItem,
        private readonly array $languageItemVariables = [],
    ) {}

    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.user' . \ucfirst($this->propertyName);
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return $this->languageItem;
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->getDynamicVariable($this->languageItem, $this->languageItemVariables);
    }

    #[\Override]
    protected function createFormField(): NumericRangeFormField
    {
        return NumericRangeFormField::create('user' . \ucfirst($this->propertyName))
            ->label($this->languageItem, $this->languageItemVariables)
            ->integerValues()
            ->minimum(0);
    }

    #[\Override]
    protected function unserializeBound(string $bound): int
    {
        return (int)$bound;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        [$from, $to] = $value;

        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.user.integerProperty.summary', [
            'title' => $this->getTitle(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    #[\Override]
    public function applyFilter(PreparedStatementConditionBuilder $conditions, mixed $value): void
    {
        [$from, $to] = $value;

        if ($from !== null) {
            $conditions->add($this->propertyName . ' >= ?', [$from]);
        }
        if ($to !== null) {
            $conditions->add($this->propertyName . ' <= ?', [$to]);
        }
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        if ($object->isGuest()) {
            return false;
        }

        [$from, $to] = $configuredValue;
        $value = $object->{$this->propertyName};

        if ($from !== null && $value < $from) {
            return false;
        }
        if ($to !== null && $value > $to) {
            return false;
        }

        return true;
    }
}
