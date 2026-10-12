<?php

namespace wcf\data\user\option;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\user\User;
use wcf\system\cache\builder\UserOptionCacheBuilder;

/**
 * Represents a viewable user option.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `UserProfile::getFormattedUserOption()` or `UserOption::getFormattedValue()` instead.
 *
 * @mixin   UserOption
 * @extends DatabaseObjectDecorator<UserOption>
 */
class ViewableUserOption extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserOption::class;

    /**
     * cached user options
     * @var ViewableUserOption[]
     */
    public static $userOptions = [];

    /**
     * user option value
     * @var string
     */
    public $optionValue = '';

    /**
     * Sets option values for a specific user.
     *
     * @return void
     */
    public function setOptionValue(User $user)
    {
        $optionValue = (string)$user->{$this->getIdentifier()};
        if ($optionValue === '' || $optionValue === '0000-00-00') {
            $this->optionValue = '';

            return;
        }

        $this->optionValue = $this->getDecoratedObject()->getFormattedValue($optionValue, $user);
    }

    /**
     * Returns the user option with the given name
     *
     * @return  ViewableUserOption
     */
    public static function getUserOption(string $name)
    {
        if (!isset(self::$userOptions[$name])) {
            $options = UserOptionCacheBuilder::getInstance()->getData([], 'options');
            self::$userOptions[$name] = new self($options[$name]);
        }

        return self::$userOptions[$name];
    }
}
