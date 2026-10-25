<?php

namespace wcf\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\user\DisableUserAvatar;
use wcf\data\user\User;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\dependency\EmptyFormFieldDependency;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;

/**
 * Disables the avatar of a user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserDisableAvatarAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $parameters = Helper::mapQueryParameters(
                $request->getQueryParams(),
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $user = Helper::fetchObjectFromRequestParameter($parameters['id'], User::class);
        if (!$user->canDisableAvatar()) {
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
            $disableAvatarExpires = 0;
            if (!(bool)$data['disableAvatarNeverExpires']) {
                $disableAvatarExpires = $data['disableAvatarExpires'];
            }

            if ($user->disableAvatar === 0) {
                new DisableUserAvatar($user, $data['disableAvatarReason'], $disableAvatarExpires)();
            }

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.acp.user.disableAvatar')
        );
        $form->appendChildren([
            MultilineTextFormField::create('disableAvatarReason')
                ->label('wcf.acp.user.disableAvatarReason')
                ->maximumLength(65000),
            BooleanFormField::create('disableAvatarNeverExpires')
                ->label('wcf.acp.user.disableAvatar.neverExpires')
                ->value(true),
            DateFormField::create('disableAvatarExpires')
                ->label('wcf.acp.user.disableAvatar.expires')
                ->description('wcf.acp.user.disableAvatar.expires.description')
                ->earliestDate(\TIME_NOW)
                ->required()
                ->addDependency(
                    EmptyFormFieldDependency::create('disableAvatarNeverExpires')
                        ->fieldId('disableAvatarNeverExpires')
                ),
        ]);
        $form->build();

        return $form;
    }
}
