<?php

namespace wcf\system\endpoint\controller\core\captchas\questions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\captcha\question\CaptchaQuestion;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
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

        $this->assertQuestionCanBeDisabled();

        if (!$question->isDisabled) {
            (new \wcf\command\captcha\question\DisableCaptchaQuestion($question))();
        }

        return new JsonResponse([]);
    }

    private function assertQuestionCanBeDisabled(): void
    {
        WCF::getSession()->checkPermissions(['admin.captcha.canManageCaptchaQuestion']);
    }
}
