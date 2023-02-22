<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\message\censorship\Censorship;
use wcf\system\WCF;

/**
 * Option type implementation for the 'about me' text field.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AboutMeOptionType extends MessageOptionType
{
    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue)
    {
        parent::validate($option, $newValue);

        $htmlContent = $this->htmlInputProcessor->getHtml();
        $textContent = $this->htmlInputProcessor->getTextContent();

        if (\mb_strlen($textContent) > WCF::getSession()->getPermission('user.profile.aboutMeMaxLength')) {
            throw new UserInputException($option->optionName, 'tooLong');
        }

        // There is a hardlimit in the database column size.
        if (\mb_strlen($htmlContent) > 65000) {
            throw new UserInputException($option->optionName, 'tooLong');
        }

        $censoredWords = Censorship::getInstance()->test($textContent);
        if ($censoredWords) {
            WCF::getTPL()->assign('censoredWords', $censoredWords);
            throw new UserInputException($option->optionName, 'censoredWordsFound');
        }
    }
}
