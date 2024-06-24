<?php

namespace wcf\system\option\user\group;

use wcf\data\bbcode\BBCodeCache;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\option\AbstractOptionType;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * User group option type implementation for BBCode select lists.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class BBCodeSelectUserGroupOptionType extends AbstractOptionType implements IUserGroupOptionType
{
    /**
     * available BBCodes
     * @var string[]
     */
    protected $bbCodes;

    /**
     * list of bbcode tags that are always available
     * @var string[]
     */
    protected static $alwaysAvailable = [
        'align',
        'attach',
        'b',
        'code',
        'i',
        'list',
        'quote',
        's',
        'sub',
        'sup',
        'table',
        'td',
        'tr',
        'tt',
        'u',
        'user',
        'wsm',
        'wsmg',
        'wsp',
    ];

    /**
     * @inheritDoc
     */
    public function getData(Option $option, $newValue)
    {
        if (!\is_array($newValue)) {
            $newValue = [];
        }

        return \implode(',', $newValue);
    }

    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        if ($this->bbCodes === null) {
            $this->loadBBCodeSelection();
        }

        WCF::getTPL()->assign([
            'bbCodes' => $this->bbCodes,
            'option' => $option,
            'selectedBBCodes' => \explode(',', $value ?: ''),
        ]);

        return WCF::getTPL()->fetch('bbCodeSelectOptionType');
    }

    /**
     * Loads the list of BBCodes for the HTML select element.
     */
    protected function loadBBCodeSelection()
    {
        $this->bbCodes = \array_keys(BBCodeCache::getInstance()->getBBCodes());
        $this->bbCodes = \array_diff($this->bbCodes, self::$alwaysAvailable);

        \asort($this->bbCodes);
    }

    /**
     * @inheritDoc
     */
    public function merge($defaultValue, $groupValue)
    {
        if ($this->bbCodes === null) {
            $this->loadBBCodeSelection();
        }

        if (empty($defaultValue)) {
            $defaultValue = [];
        } else {
            $defaultValue = \explode(',', StringUtil::unifyNewlines($defaultValue));
        }

        if (empty($groupValue)) {
            $groupValue = [];
        } else {
            $groupValue = \explode(',', StringUtil::unifyNewlines($groupValue));
        }

        $newValue = \array_intersect($defaultValue, $groupValue);
        \sort($newValue);

        return \implode(',', $newValue);
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue)
    {
        if (!\is_array($newValue)) {
            $newValue = [];
        }

        if ($this->bbCodes === null) {
            $this->loadBBCodeSelection();
        }

        foreach ($newValue as $tag) {
            if (!\in_array($tag, $this->bbCodes)) {
                throw new UserInputException($option->optionName, 'validationFailed');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function compare($value1, $value2)
    {
        // handle special case where no disallowed BBCodes have been set
        if (empty($value1)) {
            if (empty($value2)) {
                return 0;
            }

            return 1;
        } elseif (empty($value2)) {
            return 1;
        }

        $value1 = \explode(',', $value1);
        $value2 = \explode(',', $value2);

        // check if value1 disallows more BBCodes than value2
        $diff = \array_diff($value1, $value2);
        if (!empty($diff)) {
            return -1;
        }

        // check if value1 disallows less BBCodes than value2
        $diff = \array_diff($value2, $value1);
        if (!empty($diff)) {
            return 1;
        }

        // both lists of BBCodes are equal
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getCSSClassName()
    {
        return 'checkboxList';
    }
}
