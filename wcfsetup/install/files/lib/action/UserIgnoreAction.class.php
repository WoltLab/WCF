<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\ignore\UserIgnore;
use wcf\data\user\ignore\UserIgnoreAction as IgnoreUserIgnoreAction;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;

/**
 * Handles user ignores.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class UserIgnoreAction implements RequestHandlerInterface
{
    /**
     * @inheritDoc
     */
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

        $this->assertUserIsLoggedIn();

        $user = UserProfileRuntimeCache::getInstance()->getObject($parameters['id']);
        $ignore = UserIgnore::getIgnore($parameters['id']);
        $this->assertTargetCanBeIgnored($user, $ignore);

        $form = $this->getForm($user, $ignore);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $type = \intval($form->getData()['data']['type']);

            if ($type === UserIgnore::TYPE_NO_IGNORE) {
                (new IgnoreUserIgnoreAction([], 'unignore', [
                    'data' => [
                        'userID' => $parameters['id'],
                    ],
                ]))->executeAction();
            } else {
                (new IgnoreUserIgnoreAction([], 'ignore', [
                    'data' => [
                        'userID' => $parameters['id'],
                        'type' => $type,
                    ],
                ]))->executeAction();
            }

            return new JsonResponse([
                'result' => [
                    'type' => $type,
                ],
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function assertUserIsLoggedIn(): void
    {
        if (!WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }

    private function assertTargetCanBeIgnored(?UserProfile $target, UserIgnore $ignore): void
    {
        if (!$target) {
            throw new IllegalLinkException();
        }

        if ($target->userID === WCF::getUser()->userID) {
            throw new IllegalLinkException();
        }

        // Check if the user is not yet ignored and cannot be ignored.
        if ($ignore->type == UserIgnore::TYPE_NO_IGNORE && $target->getPermission('user.profile.cannotBeIgnored')) {
            throw new PermissionDeniedException();
        }
    }

    private function getForm(UserProfile $user, UserIgnore $ignore): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.user.ignore.type')
        );
        $form->appendChildren([
            RadioButtonFormField::create('type')
                ->required()
                ->options([
                    UserIgnore::TYPE_NO_IGNORE => WCF::getLanguage()
                        ->get('wcf.user.ignore.type.noIgnore'),
                    UserIgnore::TYPE_BLOCK_DIRECT_CONTACT => WCF::getLanguage()
                        ->get('wcf.user.ignore.type.blockDirectContact'),
                    UserIgnore::TYPE_HIDE_MESSAGES => WCF::getLanguage()
                        ->get('wcf.user.ignore.type.hideMessages'),
                ])
                ->value($ignore->type ?: 0)
                ->addValidator(new FormFieldValidator('type', function (RadioButtonFormField $formField) use ($user) {
                    if ($user->getPermission('user.profile.cannotBeIgnored')) {
                        if ($formField->getValue() != UserIgnore::TYPE_NO_IGNORE) {
                            $formField->addValidationError(
                                new FormFieldValidationError(
                                    'cannotBeIgnored',
                                    'wcf.user.ignore.error.cannotBeIgnored'
                                )
                            );
                        }
                    }
                })),
        ]);

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
