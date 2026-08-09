<?php

namespace wcf\system\interaction\bulk\admin;

use wcf\data\captcha\question\CaptchaQuestionList;
use wcf\event\interaction\bulk\admin\CaptchaQuestionBulkInteractionCollecting;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\AbstractBulkInteractionProvider;
use wcf\system\interaction\bulk\BulkDeleteInteraction;
use wcf\system\WCF;

/**
 * Bulk interaction provider for captcha questions.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class CaptchaQuestionBulkInteractions extends AbstractBulkInteractionProvider
{
    public function __construct()
    {
        if (!WCF::getSession()->getPermission('admin.captcha.canManageCaptchaQuestion')) {
            return;
        }

        $this->addInteractions([
            new BulkDeleteInteraction('core/captchas/questions/%s'),
        ]);

        EventHandler::getInstance()->fire(
            new CaptchaQuestionBulkInteractionCollecting($this)
        );
    }

    #[\Override]
    public function getObjectListClassName(): string
    {
        return CaptchaQuestionList::class;
    }
}
