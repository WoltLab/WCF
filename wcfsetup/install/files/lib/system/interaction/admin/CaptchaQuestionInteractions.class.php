<?php

namespace wcf\system\interaction\admin;

use wcf\data\captcha\question\CaptchaQuestion;
use wcf\event\interaction\admin\CaptchaQuestionInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\AbstractInteractionProvider;
use wcf\system\interaction\DeleteInteraction;
use wcf\system\WCF;

/**
 * Interaction provider for captcha questions.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class CaptchaQuestionInteractions extends AbstractInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.captcha.canManageCaptchaQuestion')) {
            return;
        }

        $this->addInteractions([
            new DeleteInteraction('core/captchas/questions/%s')
        ]);

        EventHandler::getInstance()->fire(
            new CaptchaQuestionInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectClassName(): string
    {
        return CaptchaQuestion::class;
    }
}
