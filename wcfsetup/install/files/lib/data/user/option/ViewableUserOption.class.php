<?php

namespace wcf\data\user\option;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\user\User;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\exception\ClassNotFoundException;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\SystemException;
use wcf\system\option\user\IUserOptionOutput;
use wcf\util\StringUtil;

/**
 * Represents a viewable user option.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserOption  getDecoratedObject()
 * @mixin   UserOption
 */
class ViewableUserOption extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserOption::class;

    /**
     * list of output objects
     * @var IUserOptionOutput[]
     */
    public static $outputObjects = [];

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
     * @param User $user
     */
    public function setOptionValue(User $user)
    {
        $userOption = 'userOption' . $this->optionID;
        $optionValue = $user->{$userOption};

        // use output class
        if ($this->outputClass) {
            $outputObj = $this->getOutputObject();
            $this->optionValue = $outputObj->getOutput($user, $this->getDecoratedObject(), $optionValue);
        } else {
            $this->optionValue = StringUtil::encodeHTML($optionValue);
        }
    }

    /**
     * Returns the output object for current user option.
     *
     * @return  IUserOptionOutput
     * @throws  SystemException
     */
    public function getOutputObject()
    {
        if (!isset(self::$outputObjects[$this->outputClass])) {
            // create instance
            if (!\class_exists($this->outputClass)) {
                throw new ClassNotFoundException($this->outputClass);
            }

            // validate interface
            if (!\is_subclass_of($this->outputClass, IUserOptionOutput::class)) {
                throw new ImplementationException($this->outputClass, IUserOptionOutput::class);
            }

            self::$outputObjects[$this->outputClass] = new $this->outputClass();
        }

        return self::$outputObjects[$this->outputClass];
    }

    /**
     * Returns the user option with the given name
     *
     * @param string $name
     * @return  ViewableUserOption
     */
    public static function getUserOption($name)
    {
        if (!isset(self::$userOptions[$name])) {
            $options = UserOptionCacheBuilder::getInstance()->getData([], 'options');
            self::$userOptions[$name] = new self($options[$name]);
        }

        return self::$userOptions[$name];
    }
}
