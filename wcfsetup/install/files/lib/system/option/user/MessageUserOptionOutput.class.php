<?php

namespace wcf\system\option\user;

use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * User option output implementation for a formatted textarea value.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Option\User
 */
class MessageUserOptionOutput implements IUserOptionOutput
{
    /**
     * @inheritDoc
     */
    public function getOutput(User $user, UserOption $option, $value)
    {
        $value = StringUtil::trim($value);
        if (empty($value)) {
            return '';
        }

        // Load embedded objects by parsing the original message again because for the user options, there
        // is no central way to save the embedded objects in the database.
        $htmlInputProcessor = new HtmlInputProcessor();
        $htmlInputProcessor->setContext('com.woltlab.wcf.user.aboutMe', $user->userID);
        $htmlInputProcessor->processIntermediate($value);
        $htmlInputProcessor->processEmbeddedContent($value, 'com.woltlab.wcf.user.aboutMe', $user->userID);
        MessageEmbeddedObjectManager::getInstance()->registerTemporaryMessage($htmlInputProcessor);

        $htmlOutputProcessor = new HtmlOutputProcessor();
        $htmlOutputProcessor->process($value, 'com.woltlab.wcf.user.aboutMe', $user->userID);

        WCF::getTPL()->assign([
            'option' => $option,
            'value' => $htmlOutputProcessor->getHtml(),
        ]);

        return WCF::getTPL()->fetch('messageUserOptionOutput');
    }
}
