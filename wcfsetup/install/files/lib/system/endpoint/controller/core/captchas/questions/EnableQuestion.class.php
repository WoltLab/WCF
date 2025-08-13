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
 * Enables the captcha question with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/captchas/questions/{id:\d+}/enable')]
final class EnableQuestion implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $question = Helper::fetchObjectFromRequestParameter($variables['id'], CaptchaQuestion::class);

        $this->assertQuestionCanBeEnabled();

        if ($question->isDisabled) {
            (new \wcf\command\captcha\question\EnableCaptchaQuestion($question))();
        }

        return new JsonResponse([]);
    }

    private function assertQuestionCanBeEnabled(): void
    {
        WCF::getSession()->checkPermissions(['admin.captcha.canManageCaptchaQuestion']);
    }
}
