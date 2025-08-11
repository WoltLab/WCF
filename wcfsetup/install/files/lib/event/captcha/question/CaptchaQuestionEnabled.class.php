<?php

namespace wcf\event\captcha\question;

use wcf\data\captcha\question\CaptchaQuestion;
use wcf\event\IPsr14Event;

/**
 * Indicates that a captcha question has been enabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class CaptchaQuestionEnabled implements IPsr14Event
{
    public function __construct(public readonly CaptchaQuestion $captchaQuestion) {}
}
