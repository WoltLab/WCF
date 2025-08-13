<?php

namespace wcf\command\captcha\question;

use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionEditor;
use wcf\event\captcha\question\CaptchaQuestionDisabled;
use wcf\system\event\EventHandler;

/**
 * Disables the given captcha question.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableCaptchaQuestion
{
    public function __construct(
        private readonly CaptchaQuestion $captchaQuestion,
    ) {}

    public function __invoke(): void
    {
        (new CaptchaQuestionEditor($this->captchaQuestion))->update([
            'isDisabled' => 1,
        ]);

        $event = new CaptchaQuestionDisabled($this->captchaQuestion);
        EventHandler::getInstance()->fire($event);
    }
}
