<?php

namespace wcf\data\user\option;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Provides functions to edit user options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   UserOption
 * @extends DatabaseObjectEditor<UserOption>
 * @implements IEditableCachedObject<UserOption>
 */
class UserOptionEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserOption::class;

    #[\Override]
    public static function create(array $parameters = [])
    {
        if (!\array_key_exists('configuration', $parameters)) {
            $parameters['configuration'] = self::buildConfiguration(
                $parameters['selectOptions'] ?? '',
                (bool)($parameters['required'] ?? false)
            );
        }

        /** @var UserOption $userOption */
        $userOption = parent::create($parameters);

        // alter the table "wcf1_user_option_value" with this new option
        WCF::getDB()->getEditor()->addColumn(
            'wcf1_user_option_value',
            'userOption' . $userOption->optionID,
            self::getColumnDefinition($parameters['optionType'])
        );

        // add the default value to this column
        if (isset($parameters['defaultValue'])) {
            $sql = "UPDATE  wcf1_user_option_value
                    SET     userOption" . $userOption->optionID . " = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$parameters['defaultValue']]);
        }

        return $userOption;
    }

    #[\Override]
    public function update(array $parameters = [])
    {
        if (
            !\array_key_exists('configuration', $parameters)
            && (\array_key_exists('selectOptions', $parameters) || \array_key_exists('required', $parameters))
        ) {
            $parameters['configuration'] = self::buildConfiguration(
                $parameters['selectOptions'] ?? $this->selectOptions ?? '',
                (bool)($parameters['required'] ?? $this->required)
            );
        }

        parent::update($parameters);

        // alter the table "wcf1_user_option_value" with this new option
        if (isset($parameters['optionType']) && $parameters['optionType'] !== $this->optionType) {
            WCF::getDB()->getEditor()->alterColumn(
                'wcf1_user_option_value',
                'userOption' . $this->optionID,
                'userOption' . $this->optionID,
                self::getColumnDefinition($parameters['optionType'])
            );
        }
    }

    #[\Override]
    public function delete()
    {
        $sql = "DELETE FROM wcf1_user_option
                WHERE       optionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->optionID]);

        WCF::getDB()->getEditor()->dropColumn('wcf1_user_option_value', 'userOption' . $this->optionID);
    }

    #[\Override]
    public static function deleteAll(array $objectIDs = [])
    {
        $returnValue = parent::deleteAll($objectIDs);

        foreach ($objectIDs as $objectID) {
            WCF::getDB()->getEditor()->dropColumn('wcf1_user_option_value', 'userOption' . $objectID);
        }

        return $returnValue;
    }

    /**
     * Disables this option.
     *
     * @return void
     */
    public function disable()
    {
        $this->enable(false);
    }

    /**
     * Enables this option.
     *
     * @return void
     */
    public function enable(bool $enable = true)
    {
        $value = \intval(!$enable);

        $sql = "UPDATE  wcf1_user_option
                SET     isDisabled = ?
                WHERE   optionID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$value, $this->optionID]);
    }

    /**
     * Determines the needed sql statement to modify column definitions.
     *
     * @return array{
     *  autoIncrement: bool,
     *  key: false,
     *  notNull: bool,
     *  type: string,
     *  default?: int|float|string,
     *  length?: int,
     * } column definition
     */
    public static function getColumnDefinition(string $optionType)
    {
        $column = [
            'autoIncrement' => false,
            'key' => false,
            'notNull' => false,
            'type' => 'text',
        ];

        switch ($optionType) {
            case 'boolean':
                $column['notNull'] = true;
                $column['default'] = 0;
                $column['type'] = 'tinyint';
                break;

            case 'integer':
                $column['notNull'] = true;
                $column['default'] = 0;
                $column['type'] = 'int';
                break;

            case 'float':
                $column['notNull'] = true;
                $column['default'] = 0.0;
                $column['type'] = 'float';
                break;

            case 'textarea':
                $column['type'] = 'mediumtext';
                break;

            case 'birthday':
            case 'date':
                $column['notNull'] = true;
                $column['default'] = "'0000-00-00'";
                $column['length'] = 10;
                $column['type'] = 'char';
                break;
        }

        return $column;
    }

    /**
     * Builds the JSON-encoded form option configuration from the legacy option data.
     *
     * @since 6.3
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

    #[\Override]
    public static function resetCache()
    {
        UserOptionCacheBuilder::getInstance()->reset();
    }
}
