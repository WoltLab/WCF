<?php

namespace wcf\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\user\DisableUserSignature;
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
 * Disables the signature of a user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserDisableSignatureAction implements RequestHandlerInterface
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
        if (!$user->canDisableSignature()) {
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
            $disableSignatureExpires = 0;
            if (!(bool)$data['disableSignatureNeverExpires']) {
                $disableSignatureExpires = $data['disableSignatureExpires'];
            }

            if ($user->disableSignature === 0) {
                new DisableUserSignature($user, $data['disableSignatureReason'], $disableSignatureExpires)();
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
            WCF::getLanguage()->get('wcf.acp.user.disableSignature')
        );
        $form->appendChildren([
            MultilineTextFormField::create('disableSignatureReason')
                ->label('wcf.acp.user.disableSignatureReason')
                ->maximumLength(65000),
            BooleanFormField::create('disableSignatureNeverExpires')
                ->label('wcf.acp.user.disableSignature.neverExpires')
                ->value(true),
            DateFormField::create('disableSignatureExpires')
                ->label('wcf.acp.user.disableSignature.expires')
                ->description('wcf.acp.user.disableSignature.expires.description')
                ->earliestDate(\TIME_NOW)
                ->required()
                ->addDependency(
                    EmptyFormFieldDependency::create('disableSignatureNeverExpires')
                        ->fieldId('disableSignatureNeverExpires')
                ),
        ]);
        $form->build();

        return $form;
    }
}
