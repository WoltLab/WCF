<?php

namespace wcf\event\captcha\question;

use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a captcha question has been created.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CaptchaQuestionCreated implements IPsr14Event
{
    public function __construct(
        public readonly CaptchaQuestion $captchaQuestion,
        public readonly CaptchaQuestionBuilder $builder,
    ) {}
}
