<?php

namespace wcf\system\endpoint\controller\core\captchas\questions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\captcha\question\CaptchaQuestion;
use wcf\data\captcha\question\CaptchaQuestionAction;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Disables the captcha question with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/captchas/questions/{id:\d+}/disable')]
final class DisableQuestion implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $question = Helper::fetchObjectFromRequestParameter($variables['id'], CaptchaQuestion::class);

        $this->assertQuestionCanBeDisabled($question);

        (new CaptchaQuestionAction([$question], 'toggle'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertQuestionCanBeDisabled(CaptchaQuestion $question): void
    {
        WCF::getSession()->checkPermissions(['admin.captcha.canManageCaptchaQuestion']);

        if ($question->isDisabled) {
            throw new PermissionDeniedException();
        }
    }
}
