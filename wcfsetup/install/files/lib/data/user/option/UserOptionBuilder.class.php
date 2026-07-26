<?php

namespace wcf\data\user\option;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Builder for creating, updating and deleting user options.
 *
 * The option's localized name and description are not handled here; they are
 * stored as i18n phrases (`wcf.user.option.<optionName>[.description]`) by the
 * calling form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserOption>
 */
final class UserOptionBuilder extends DatabaseObjectBuilder
{
    private bool $isGenericOptionName = false;

    public function setOptionName(string $optionName): static
    {
        if ($this->isUpdate() && !\str_starts_with($this->getObject()->optionName, 'tmp_')) {
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

    public function setSelectOptions(string $selectOptions): static
    {
        $this->properties['selectOptions'] = $selectOptions;

        return $this;
    }

    public function setEnableOptions(string $enableOptions): static
    {
        $this->properties['enableOptions'] = $enableOptions;

        return $this;
    }

    public function setLabeledUrl(string $labeledUrl): static
    {
        $this->properties['labeledUrl'] = $labeledUrl;

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

    public function setOutputClass(string $outputClass): static
    {
        $this->properties['outputClass'] = $outputClass;

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
     * @param array<string, mixed> $additionalData
     */
    public function setAdditionalData(array $additionalData): static
    {
        $this->properties['additionalData'] = \serialize($additionalData);

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['optionName', 'optionType', 'categoryName'];
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        // add the dynamic value column for this option
        WCF::getDB()->getEditor()->addColumn(
            'wcf1_user_option_value',
            'userOption' . $object->optionID,
            UserOptionEditor::getColumnDefinition($object->optionType)
        );

        // apply the default value to all existing rows
        if ($object->defaultValue !== null) {
            $sql = "UPDATE  wcf1_user_option_value
                    SET     userOption" . $object->optionID . " = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$object->defaultValue]);
        }

        // assign the generic option name now that the id is known
        if ($this->isGenericOptionName) {
            UserOptionBuilder::forUpdate($object)
                ->setOptionName('option' . $object->optionID)
                ->update();
        }
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        // re-type the value column if the option type changed
        if ($object->optionType !== $this->getObject()->optionType) {
            WCF::getDB()->getEditor()->alterColumn(
                'wcf1_user_option_value',
                'userOption' . $object->optionID,
                'userOption' . $object->optionID,
                UserOptionEditor::getColumnDefinition($object->optionType)
            );
        }
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        foreach ($objectIDs as $objectID) {
            WCF::getDB()->getEditor()->dropColumn('wcf1_user_option_value', 'userOption' . $objectID);
        }
    }
}
