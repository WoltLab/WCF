<?php

namespace wcf\system\option;

use wcf\data\custom\option\CustomOption;
use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\system\exception\NotImplementedException;
use wcf\system\exception\UserInputException;

/**
 * Default implementation for custom option handling.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template TOption of CustomOption = CustomOption
 * @extends OptionHandler<TOption, OptionCategory>
 * @phpstan-import-type ParsedOption from OptionHandler
 * @deprecated 6.2 Use `IFormOption` instead
 */
abstract class CustomOptionHandler extends OptionHandler
{
    /**
     * Gets all options and option categories from cache.
     */
    #[\Override]
    protected function readCache()
    {
        throw new NotImplementedException();
    }

    /**
     * Initializes active options.
     */
    #[\Override]
    public function init()
    {
        if (!$this->didInit) {
            // get active options
            foreach ($this->cachedOptions as $option) {
                if ($this->checkOption($option)) {
                    $this->options[$option->optionName] = $option;
                }
            }

            // mark options as initialized
            $this->didInit = true;
        }
    }

    /**
     * Returns the parsed options.
     *
     * @return list<ParsedOption>
     */
    public function getOptions()
    {
        $parsedOptions = [];
        foreach ($this->options as $option) {
            $parsedOptions[] = $this->getOption($option->optionName);
        }

        return $parsedOptions;
    }

    #[\Override]
    public function readData()
    {
        foreach ($this->options as $option) {
            if (!isset($this->optionValues[$option->optionName])) {
                $this->optionValues[$option->optionName] = $option->defaultValue;
            }
        }
    }

    /**
     * Resets the option values.
     *
     * @return void
     */
    public function resetOptionValues()
    {
        $this->optionValues = [];
    }

    /**
     * Returns the option values.
     *
     * @return array<string, string>
     */
    public function getOptionValues()
    {
        return $this->optionValues;
    }

    /**
     * Sets the option values.
     *
     * @param array<string, string> $values
     * @return void
     */
    public function setOptionValues(array $values)
    {
        $this->optionValues = $values;
    }

    #[\Override]
    public function getOption(string $optionName)
    {
        $optionData = parent::getOption($optionName);

        if (isset($this->optionValues[$optionName])) {
            $optionData['object']->setOptionValue($this->optionValues[$optionName]);
        }

        return $optionData;
    }

    #[\Override]
    protected function validateOption(Option $option)
    {
        parent::validateOption($option);

        if ($option->required && empty($this->optionValues[$option->optionName])) {
            throw new UserInputException($option->optionName);
        }
    }
}
