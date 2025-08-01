<?php

namespace wcf\system\endpoint\controller\core\captchas\questions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\WCF;

/**
 * Deletes the captcha question with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/captchas/questions/{id:\d+}')]
final class DeleteQuestion implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $question = Helper::fetchObjectFromRequestParameter($variables['id'], CaptchaQuestion::class);

        $this->assertQuestionCanBeDeleted();

        (new CaptchaQuestionAction([$question], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertQuestionCanBeDeleted(): void
    {
        WCF::getSession()->checkPermissions(['admin.captcha.canManageCaptchaQuestion']);
    }
}
