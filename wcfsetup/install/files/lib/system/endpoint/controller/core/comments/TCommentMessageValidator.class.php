<?php

namespace wcf\system\endpoint\controller\core\comments;

use wcf\system\bbcode\BBCodeHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\WCF;
use wcf\util\MessageUtil;

/**
 * Trait that provides helper methods for comment controllers.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
trait TCommentMessageValidator
{
    private function validateMessage(string $message, bool $isResponse = false, int $objectID = 0): HtmlInputProcessor
    {
        $message = MessageUtil::stripCrap($message);
        if ($message === '') {
            throw new UserInputException('message');
        }

        CommentHandler::enforceCensorship($message);

        BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
            ',',
            WCF::getSession()->getPermission('user.comment.disallowedBBCodes')
        ));

        $htmlInputProcessor = new HtmlInputProcessor();
        if ($isResponse) {
            $htmlInputProcessor->process(
                $message,
                'com.woltlab.wcf.comment.response',
                $objectID
            );
        } else {
            $htmlInputProcessor->process(
                $message,
                'com.woltlab.wcf.comment',
                $objectID
            );
        }

        // search for disallowed bbcodes
        $disallowedBBCodes = $htmlInputProcessor->validate();
        if (!empty($disallowedBBCodes)) {
            throw new UserInputException(
                'text',
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.message.error.disallowedBBCodes',
                    ['disallowedBBCodes' => $disallowedBBCodes]
                )
            );
        }

        if ($htmlInputProcessor->appearsToBeEmpty()) {
            throw new UserInputException('message');
        }

        $commentTextContent = $htmlInputProcessor->getTextContent();
        if (\mb_strlen($commentTextContent) > WCF::getSession()->getPermission('user.comment.maxLength')) {
            throw new UserInputException(
                'text',
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.message.error.tooLong',
                    ['maxTextLength' => WCF::getSession()->getPermission('user.comment.maxLength')]
                )
            );
        }

        return $htmlInputProcessor;
    }
}
