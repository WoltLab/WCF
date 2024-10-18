<?php

namespace wcf\data\option;

use wcf\data\DatabaseObject;
use wcf\data\TDatabaseObjectOptions;
use wcf\data\TDatabaseObjectPermissions;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Represents an option.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $optionID       unique id of the option
 * @property-read   int $packageID      id of the package the which delivers the option
 * @property-read   string $optionName     name and textual identifier of the option
 * @property-read   string $categoryName       name of the option category the option belongs to
 * @property-read   string $optionType     textual identifier of the option (corresponds to a class implementing `wcf\system\option\IOptionType`)
 * @property-read   string $optionValue        value of the option
 * @property-read   string $validationPattern  regular expression used to validate the option's value or empty if no such regular expression exists
 * @property-read   string $selectOptions      newline-separated list of selectable options for a selectable option type (line pattern: `{value}:{language item name}`)
 * @property-read   string $enableOptions      list of options that are enabled based on the option's value (simple comma-separated list of boolean options, otherwise newline-separated list with line pattern: `{select value}:{comma-separated list}`)
 * @property-read   int $showOrder      position of the option in relation to the other option in the option category
 * @property-read   int $hidden         is `1` if the option is hidden and thus cannot be explicitly set by in the acp, otherwise `0`
 * @property-read   string $permissions        comma separated list of user group permissions of which the active user needs to have at least one to set the option value
 * @property-read   string $options        comma separated list of options of which at least one needs to be enabled for the option to be editable
 * @property-read   int $supportI18n        is `1` if the option supports different values for all available languages, otherwise `0`
 * @property-read   int $requireI18n        is `1` if `$supportI18n = 1` and the option's value has to explicitly set for all values so that the `monolingual` option is not available, otherwise `0`
 * @property-read   array $additionalData     array with additional data of the option
 */
class Option extends DatabaseObject
{
    use TDatabaseObjectOptions;
    use TDatabaseObjectPermissions;

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        $value = $this->data[$name] ?? null;

        // treat additional data as data variables if it is an array
        if ($value === null) {
            if (\is_array($this->data['additionalData']) && isset($this->data['additionalData'][$name])) {
                $value = $this->data['additionalData'][$name];
            }
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    protected function handleData($data)
    {
        parent::handleData($data);

        // unserialize additional data
        $this->data['additionalData'] = (empty($data['additionalData']) ? [] : @\unserialize($data['additionalData']));
    }

    /**
     * Returns a list of options.
     *
     * @return  static[]
     */
    public static function getOptions(): array
    {
        $sql = "SELECT  *
                FROM    " . self::getDatabaseTableName();
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        $options = [];
        while ($row = $statement->fetchArray()) {
            $option = new static(null, $row);
            $options[$option->getConstantName()] = $option;
        }

        return $options;
    }

    /**
     * Returns the option with the given name or `null` if no such option exists.
     */
    public static function getOptionByName(string $optionName): static|null
    {
        $sql = "SELECT  *
                FROM    " . self::getDatabaseTableName() . "
                WHERE   optionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$optionName]);

        return $statement->fetchObject(static::class);
    }

    /**
     * Parses enableOptions.
     *
     * @param string $optionData
     * @return  array
     */
    public static function parseEnableOptions($optionData)
    {
        $disableOptions = $enableOptions = '';

        if (!empty($optionData)) {
            $options = ArrayUtil::trim(\explode(',', $optionData));

            foreach ($options as $item) {
                if ($item[0] == '!') {
                    if (!empty($disableOptions)) {
                        $disableOptions .= ',';
                    }
                    $disableOptions .= "'" . \mb_substr($item, 1) . "' ";
                } else {
                    if (!empty($enableOptions)) {
                        $enableOptions .= ',';
                    }
                    $enableOptions .= "'" . $item . "' ";
                }
            }
        }

        return [
            'disableOptions' => $disableOptions,
            'enableOptions' => $enableOptions,
        ];
    }

    /**
     * Returns a list of the available options.
     *
     * @return  array
     */
    public function parseSelectOptions()
    {
        $result = [];
        $options = \explode("\n", StringUtil::trim(StringUtil::unifyNewlines($this->selectOptions)));
        foreach ($options as $option) {
            $key = $value = $option;
            if (\str_contains($option, ':')) {
                $optionData = \explode(':', $option);
                $key = \array_shift($optionData);
                $value = \implode(':', $optionData);
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Returns a list of the enable options.
     *
     * @return  array
     */
    public function parseMultipleEnableOptions()
    {
        $result = [];
        if (!empty($this->enableOptions)) {
            $options = \explode("\n", StringUtil::trim(StringUtil::unifyNewlines($this->enableOptions)));
            $key = -1;
            foreach ($options as $option) {
                if (\str_contains($option, ':')) {
                    $optionData = \explode(':', $option);
                    $key = \array_shift($optionData);
                    $value = \implode(':', $optionData);
                } else {
                    $key++;
                    $value = $option;
                }

                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Returns true if option is visible
     *
     * @return  bool
     */
    public function isVisible()
    {
        return !$this->hidden;
    }

    /**
     * @inheritDoc
     */
    public static function getDatabaseTableAlias()
    {
        return 'option_table';
    }

    /**
     * Returns the constant name.
     *
     * @return  string
     */
    public function getConstantName()
    {
        return \strtoupper($this->optionName);
    }

    /**
     * Allows modifications of select options.
     *
     * @param string $selectOptions
     */
    public function modifySelectOptions($selectOptions)
    {
        $this->data['selectOptions'] = $selectOptions;
    }

    /**
     * Allows modifications of enable options.
     *
     * @param string $enableOptions
     */
    public function modifyEnableOptions($enableOptions)
    {
        $this->data['enableOptions'] = $enableOptions;
    }

    /**
     * Allows modifications of hidden option.
     *
     * @param string $hiddenOption
     */
    public function modifyHiddenOption($hiddenOption)
    {
        $this->data['hidden'] = $hiddenOption;
    }
}
