<?php

namespace wcf\data\user\option;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\package\PackageCache;
use wcf\system\database\table\column\IDatabaseTableColumn;
use wcf\system\database\table\column\MediumtextDatabaseTableColumn;
use wcf\system\database\table\DatabaseTableChangeProcessor;
use wcf\system\database\table\PartialDatabaseTable;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Builder for creating, updating and deleting user options.
 *
 * The option's localized title and description are stored in the
 * `wcf1_user_option_l10n` table (see `L10nStorage`). `l10nIdentifier` links a
 * system option to its language variable; it is `NULL` for options created by
 * an administrator.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserOption>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class UserOptionBuilder extends DatabaseObjectBuilder
{
    private bool $isGenericOptionName = false;

    /**
     * Is `true` if the configuration has been set explicitly and must not be
     * derived from the select options and the required flag.
     */
    private bool $hasExplicitConfiguration = false;

    /**
     * @var L10nValue
     */
    private array $l10nTitle;

    /**
     * @var L10nValue
     */
    private array $l10nDescription;

    /**
     * @param L10nValue $title
     */
    public function setL10nTitle(array $title): static
    {
        $this->l10nTitle = $title;

        return $this;
    }

    /**
     * @param L10nValue $description
     */
    public function setL10nDescription(array $description): static
    {
        $this->l10nDescription = $description;

        return $this;
    }

    public function setL10nIdentifier(?string $l10nIdentifier): static
    {
        $this->properties['l10nIdentifier'] = $l10nIdentifier;

        return $this;
    }

    public function setOptionName(string $optionName): static
    {
        if ($this->isUpdate() && !\str_starts_with($this->getExistingObject()->optionName, 'tmp_')) {
            throw new \BadMethodCallException('setOptionName() is only allowed for generic option names.');
        }

        $this->properties['optionName'] = $optionName;

        return $this;
    }

    /**
     * Inserts the option with a temporary random name and renames it to
     * `option<optionID>` once the id is known (see `afterCreate()`).
     */
    public function setGenericOptionName(): static
    {
        if ($this->isUpdate()) {
            throw new \BadMethodCallException('setGenericOptionName() can only be used with forCreate().');
        }

        $this->properties['optionName'] = 'tmp_' . StringUtil::getRandomID();
        $this->isGenericOptionName = true;

        return $this;
    }

    public function setPackageID(int $packageID): static
    {
        $this->properties['packageID'] = $packageID;

        return $this;
    }

    public function setCategoryName(string $categoryName): static
    {
        $this->properties['categoryName'] = $categoryName;

        return $this;
    }

    /**
     * Sets the legacy option type, see `UserOption::getFormOption()` for the
     * mapping to form options.
     */
    public function setOptionType(string $optionType): static
    {
        $this->properties['optionType'] = $optionType;

        return $this;
    }

    public function setDefaultValue(string|int|float|null $defaultValue): static
    {
        $this->properties['defaultValue'] = $defaultValue;

        return $this;
    }

    public function setValidationPattern(string $validationPattern): static
    {
        $this->properties['validationPattern'] = $validationPattern;

        return $this;
    }

    /**
     * Sets the select options in the legacy format, one `value:label` pair per line.
     */
    public function setSelectOptions(string $selectOptions): static
    {
        $this->properties['selectOptions'] = $selectOptions;
        $this->syncConfiguration();

        return $this;
    }

    public function setShowOrder(int $showOrder): static
    {
        $this->properties['showOrder'] = $showOrder;

        return $this;
    }

    public function setIsDisabled(bool $isDisabled): static
    {
        $this->properties['isDisabled'] = $isDisabled ? 1 : 0;

        return $this;
    }

    public function setEditable(int $editable): static
    {
        $this->properties['editable'] = $editable;

        return $this;
    }

    public function setVisible(int $visible): static
    {
        $this->properties['visible'] = $visible;

        return $this;
    }

    public function setRequired(bool $required): static
    {
        $this->properties['required'] = $required ? 1 : 0;
        $this->syncConfiguration();

        return $this;
    }

    public function setAskDuringRegistration(bool $askDuringRegistration): static
    {
        $this->properties['askDuringRegistration'] = $askDuringRegistration ? 1 : 0;

        return $this;
    }

    public function setSearchable(bool $searchable): static
    {
        $this->properties['searchable'] = $searchable ? 1 : 0;

        return $this;
    }

    public function setShowOnUserCard(bool $showOnUserCard): static
    {
        $this->properties['showOnUserCard'] = $showOnUserCard ? 1 : 0;

        return $this;
    }

    public function setPermissions(string $permissions): static
    {
        $this->properties['permissions'] = $permissions;

        return $this;
    }

    public function setOptions(string $options): static
    {
        $this->properties['options'] = $options;

        return $this;
    }

    public function setOriginIsSystem(bool $originIsSystem): static
    {
        $this->properties['originIsSystem'] = $originIsSystem ? 1 : 0;

        return $this;
    }

    /**
     * Sets the form option configuration explicitly instead of deriving it
     * from the select options and the required flag.
     *
     * @param array<string, mixed> $configuration
     */
    public function setConfiguration(array $configuration): static
    {
        $this->hasExplicitConfiguration = true;
        $this->properties['configuration'] = $configuration !== []
            ? \json_encode($configuration, \JSON_THROW_ON_ERROR)
            : '';

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['optionName', 'optionType', 'categoryName'];
    }

    #[\Override]
    protected function afterCreate(): void
    {
        // add the dynamic value column for this option
        self::applyColumnChanges([
            $this->getObject()->getFormOption()->getDatabaseTableColumn($this->getObject()->getIdentifier()),
        ]);

        // apply the default value to all existing rows
        if ($this->getObject()->defaultValue !== null) {
            $sql = "UPDATE  wcf1_user_option_value
                    SET     userOption" . $this->getObject()->optionID . " = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->getObject()->defaultValue]);
        }

        // assign the generic option name now that the id is known
        if ($this->isGenericOptionName) {
            UserOptionBuilder::forUpdate($this->getObject())
                ->setOptionName('option' . $this->getObject()->optionID)
                ->update();
        }

        $this->saveL10nValues($this->getObject());
    }

    #[\Override]
    protected function afterUpdate(): void
    {
        // re-type the value column if the option type changed
        if ($this->getObject()->optionType !== $this->getExistingObject()->optionType) {
            self::applyColumnChanges([
                $this->getObject()->getFormOption()->getDatabaseTableColumn($this->getObject()->getIdentifier()),
            ]);
        }

        $this->saveL10nValues($this->getObject());
    }

    /**
     * Writes the localized title and description into the `*_l10n` table when
     * they have been set. Both must be provided together because
     * `L10nStorage::setValues()` replaces all rows of the object.
     */
    private function saveL10nValues(UserOption $object): void
    {
        if (!isset($this->l10nTitle) && !isset($this->l10nDescription)) {
            return;
        }
        if (!isset($this->l10nTitle) || !isset($this->l10nDescription)) {
            throw new \BadMethodCallException(
                "The localized title and description of a user option must be set together."
            );
        }

        (new L10nStorage(UserOption::getL10nDefinition()))->setValues(
            $object->optionID,
            [
                'title' => $this->l10nTitle,
                'description' => $this->l10nDescription,
            ],
        );
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        // the column type is irrelevant for dropping a column, only its name is used
        self::applyColumnChanges(\array_map(
            static fn($objectID) => MediumtextDatabaseTableColumn::create('userOption' . $objectID)->drop(),
            $objectIDs
        ));
    }

    /**
     * Applies the given column changes to the `wcf1_user_option_value` table.
     *
     * The columns are owned by `com.woltlab.wcf` regardless of the package
     * that provides the option, matching the ownership of the table itself.
     *
     * @param list<IDatabaseTableColumn> $columns
     */
    private static function applyColumnChanges(array $columns): void
    {
        $tables = [
            PartialDatabaseTable::create('wcf1_user_option_value')
                ->columns($columns),
        ];

        $processor = new DatabaseTableChangeProcessor(
            PackageCache::getInstance()->getPackageByIdentifier('com.woltlab.wcf'),
            null,
            WCF::getDB()->getEditor(),
        );
        while ($processor->process($tables)) {
        }
    }

    /**
     * Derives the configuration from the select options and the required flag,
     * falling back to the values of the updated option for the missing one.
     */
    private function syncConfiguration(): void
    {
        if ($this->hasExplicitConfiguration) {
            return;
        }

        $this->properties['configuration'] = self::buildConfiguration(
            (string)($this->properties['selectOptions'] ?? ($this->isUpdate() ? $this->getExistingObject()->selectOptions : '')),
            (bool)($this->properties['required'] ?? ($this->isUpdate() ? $this->getExistingObject()->required : false))
        );
    }

    /**
     * Builds the JSON-encoded form option configuration from the legacy option data.
     */
    public static function buildConfiguration(string $selectOptions, bool $required): string
    {
        $configuration = [];
        if ($required) {
            $configuration['required'] = 1;
        }

        $selectOptions = StringUtil::trim(StringUtil::unifyNewlines($selectOptions));
        if ($selectOptions !== '') {
            $options = [];
            foreach (\explode("\n", $selectOptions) as $selectOption) {
                $key = $value = $selectOption;
                if (\str_contains($selectOption, ':')) {
                    [$key, $value] = \explode(':', $selectOption, 2);
                }

                $options[] = [
                    'key' => $key,
                    'value' => [0 => $value],
                ];
            }

            $configuration['selectOptions'] = \json_encode($options, \JSON_THROW_ON_ERROR);
        }

        if ($configuration === []) {
            return '';
        }

        return \json_encode($configuration, \JSON_THROW_ON_ERROR);
    }
}
