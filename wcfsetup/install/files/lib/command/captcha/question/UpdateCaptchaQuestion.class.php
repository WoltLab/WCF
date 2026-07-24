<?php

namespace wcf\command\captcha\question;

use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionBuilder;
use wcf\event\captcha\question\CaptchaQuestionUpdated;
use wcf\system\cache\builder\CaptchaQuestionCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Updates a captcha question.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateCaptchaQuestion
{
    public function __construct(
        private readonly CaptchaQuestionBuilder $builder,
    ) {}

    public function __invoke(): CaptchaQuestion
    {
        $question = $this->builder->update();

        CaptchaQuestionCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new CaptchaQuestionUpdated(
            $question,
            $this->builder
        ));

        return $question;
    }
}
