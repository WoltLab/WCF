<?php

namespace wcf\command\captcha\question;

use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionBuilder;
use wcf\event\captcha\question\CaptchaQuestionCreated;
use wcf\system\cache\builder\CaptchaQuestionCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Creates a new captcha question.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateCaptchaQuestion
{
    public function __construct(
        private readonly CaptchaQuestionBuilder $builder,
    ) {}

    public function __invoke(): CaptchaQuestion
    {
        $question = $this->builder->create();

        CaptchaQuestionCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new CaptchaQuestionCreated(
            $question,
            $this->builder
        ));

        return $question;
    }
}
