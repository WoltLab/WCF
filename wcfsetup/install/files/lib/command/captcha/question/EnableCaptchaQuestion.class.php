<?php

namespace wcf\command\captcha\question;

use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionEditor;
use wcf\event\captcha\question\CaptchaQuestionEnabled;
use wcf\system\event\EventHandler;

/**
 * Enables a captcha question.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableCaptchaQuestion
{
    public function __construct(
        private readonly CaptchaQuestion $captchaQuestion,
    ) {}

    public function __invoke(): void
    {
        (new CaptchaQuestionEditor($this->captchaQuestion))->update([
            'isDisabled' => 0,
        ]);

        $event = new CaptchaQuestionEnabled($this->captchaQuestion);
        EventHandler::getInstance()->fire($event);
    }
}
