<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\CaptchaFormField;
use wcf\system\form\builder\field\user\UsernameFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Displays a dialog that guests can use to generate a guest token to authorize themselves for certain actions.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class GuestTokenDialogAction implements RequestHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];

            return new JsonResponse([
                'result' => [
                    'token' => UserUtil::createGuestToken($data['username']),
                ],
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.page.guestTokenDialog.title')
        );
        $form->appendChildren([
            UsernameFormField::create()
                ->required(),
            CaptchaFormField::create()
                ->objectType(\CAPTCHA_TYPE),
        ]);

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
