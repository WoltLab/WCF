<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\IStorableObject;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\FileProcessorFormField;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\command\user\SetAvatar;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Handles user avatars editing.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserAvatarAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    id?: positive-int
                }
                EOT
        );

        if (!WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        if (isset($parameters['id'])) {
            $user = UserProfileRuntimeCache::getInstance()->getObject($parameters['id']);
        } else {
            $user = UserProfileHandler::getInstance()->getUserProfile();
        }

        if ($user->disableAvatar && $user->userID === WCF::getUser()->userID) {
            throw new NamedUserException(HtmlString::fromSafeHtml(WCF::getLanguage()->getDynamicVariable(
                'wcf.user.avatar.error.disabled'
            )));
        }

        if (!$user->canEditAvatar()) {
            throw new PermissionDeniedException();
        }

        $form = $this->getForm($user);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];

            // If the user has already uploaded and optionally cropped an image,
            // this is already assigned to the `$user` and does not need to be saved again.
            // However, if the user wants to delete their avatar and use a standard avatar,
            // this must be saved and the cache reset
            if ($data['avatarType'] === 'none') {
                (new SetAvatar($user->getDecoratedObject()))();
            }

            // Reload the user object to get the updated avatar
            UserProfileRuntimeCache::getInstance()->removeObject($user->userID);
            $user = UserProfileRuntimeCache::getInstance()->getObject($user->userID);

            return new JsonResponse([
                'result' => [
                    'avatar' => $user->getAvatar()->getURL(),
                ],
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(UserProfile $user): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            UserAvatarAction::class,
            WCF::getLanguage()->get('wcf.user.avatar.edit')
        );
        $form->appendChildren([
            RadioButtonFormField::create('avatarType')
                ->value("none")
                ->required()
                ->options([
                    "none" => WCF::getLanguage()->get('wcf.user.avatar.type.none'),
                    "custom" => WCF::getLanguage()->get('wcf.user.avatar.type.custom'),
                ]),
            FileProcessorFormField::create('avatarFileID')
                ->objectType('com.woltlab.wcf.user.avatar')
                ->description('wcf.user.avatar.type.custom.description')
                ->required()
                ->singleFileUpload()
                ->bigPreview()
                ->simpleReplace()
                ->hideDeleteButton()
                ->thumbnailSize('128')
                ->addDependency(
                    ValueFormFieldDependency::create('avatarType')
                        ->fieldId('avatarType')
                        ->values(['custom'])
                ),
        ]);
        $form->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'avatarType',
                null,
                function (IFormDocument $document, array $data, IStorableObject $object) {
                    \assert($object instanceof UserProfile);
                    if ($object->avatarFileID === null) {
                        $data['avatarType'] = 'none';
                    } else {
                        $data['avatarType'] = 'custom';
                    }

                    return $data;
                }
            )
        );

        $form->markRequiredFields(false);
        $form->updatedObject($user);
        $form->build();

        return $form;
    }
}
