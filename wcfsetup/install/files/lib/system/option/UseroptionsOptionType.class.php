<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Option type implementation for user option selection.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UseroptionsOptionType extends AbstractOptionType
{
    /**
     * list of available user options
     * @var string[]
     */
    protected static $userOptions;

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue)
    {
        if (!\is_array($newValue)) {
            $newValue = [];
        }

        foreach ($newValue as $optionName) {
            if (!\in_array($optionName, self::getUserOptions())) {
                throw new UserInputException($option->optionName, 'validationFailed');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getData(Option $option, $newValue)
    {
        if (!\is_array($newValue)) {
            return '';
        }

        return \implode(',', $newValue);
    }

    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        $userOptions = self::getUserOptions();
        if ($option->issortable && $value) {
            $sortedOptions = \explode(',', $value);

            // remove old options
            $sortedOptions = \array_intersect($sortedOptions, $userOptions);

            // append the non-checked options after the checked and sorted options
            $userOptions = \array_merge($sortedOptions, \array_diff($userOptions, $sortedOptions));
        }

        WCF::getTPL()->assign([
            'option' => $option,
            'value' => \explode(',', $value),
            'availableOptions' => $userOptions,
        ]);

        return WCF::getTPL()->fetch('useroptionsOptionType');
    }

    /**
     * Returns the list of available user options.
     *
     * @return  string[]
     */
    protected static function getUserOptions()
    {
        if (self::$userOptions === null) {
            self::$userOptions = [];
            $sql = "SELECT  optionName
                    FROM    wcf1_user_option
                    WHERE   categoryName IN (
                                SELECT  categoryName
                                FROM    wcf1_user_option_category
                                WHERE   parentCategoryName = 'profile'
                            )
                        AND optionType <> 'boolean'";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute();
            self::$userOptions = $statement->fetchAll(\PDO::FETCH_COLUMN);
        }

        return self::$userOptions;
    }
}
