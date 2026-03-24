<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Option type implementation for textareas with i18n support.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TextareaI18nOptionType extends TextareaOptionType implements II18nOptionType
{
    /**
     * @inheritDoc
     */
    protected $supportI18n = true;

    #[\Override]
    public function getFormElement(Option $option, mixed $value)
    {
        I18nHandler::getInstance()->assignVariables(!empty($_POST));

        return WCF::getTPL()->render('wcf', 'textareaI18nOptionType', [
            'option' => $option,
            'value' => $value,
        ]);
    }

    #[\Override]
    public function validate(Option $option, mixed $newValue)
    {
        if (!I18nHandler::getInstance()->validateValue($option->optionName, (bool)$option->requireI18n, true)) {
            throw new UserInputException($option->optionName, 'validationFailed');
        }
    }
}
