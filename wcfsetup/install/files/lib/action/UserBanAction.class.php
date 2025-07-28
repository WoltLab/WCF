<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\DateFormField;
use wcf\system\form\builder\field\dependency\EmptyFormFieldDependency;
use wcf\system\form\builder\field\MultilineTextFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\user\command\Ban;
use wcf\system\WCF;

/**
 * Handles ban a user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserBanAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    id: positive-int
                }
                EOT
        );

        $user = UserProfileRuntimeCache::getInstance()->getObject($parameters['id']);
        $this->assertUserCanBeBanned($user);

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];
            $reason = $data['reason'];
            if ($data['neverExpires']) {
                $expires = null;
            } else {
                $expires = $data['expires'];
            }

            (new Ban($user->getDecoratedObject(), $reason, $expires))();

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function assertUserCanBeBanned(?UserProfile $userProfile): void
    {
        if (!$userProfile) {
            throw new IllegalLinkException();
        }

        if ($userProfile->userID === WCF::getUser()->userID) {
            throw new IllegalLinkException();
        }

        if (!WCF::getSession()->getPermission('admin.user.canBanUser')) {
            throw new PermissionDeniedException();
        }

        if (!UserGroup::isAccessibleGroup($userProfile->getGroupIDs())) {
            throw new PermissionDeniedException();
        }

        if ($userProfile->banned !== 0) {
            throw new IllegalLinkException();
        }
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->getDynamicVariable('wcf.user.ban.confirmMessage')
        );
        $form->appendChildren([
            MultilineTextFormField::create('reason')
                ->rows(3)
                ->label('wcf.global.reason')
                ->description('wcf.user.ban.reason.description'),
            BooleanFormField::create('neverExpires')
                ->label('wcf.user.ban.neverExpires')
                ->value(true),
            DateFormField::create('expires')
                ->label('wcf.user.ban.expires')
                ->description('wcf.user.ban.expires.description')
                ->earliestDate(TIME_NOW)
                ->required()
                ->addDependency(
                    EmptyFormFieldDependency::create('neverExpires')
                        ->fieldId('neverExpires')
                ),
        ]);

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
