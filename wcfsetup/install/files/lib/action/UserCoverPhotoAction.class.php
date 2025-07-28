<?php

namespace wcf\action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\cover\photo\UserCoverPhoto;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\FileProcessorFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Handles user cover photo editing.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserCoverPhotoAction implements RequestHandlerInterface
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

        if (!$user->canEditCoverPhoto()) {
            throw new PermissionDeniedException();
        }

        $form = $this->getForm($user);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(UserProfile $user): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            UserCoverPhotoAction::class,
            WCF::getLanguage()->get('wcf.user.coverPhoto.edit')
        );
        $form->appendChildren([
            FileProcessorFormField::create('coverPhotoFileID')
                ->objectType("com.woltlab.wcf.user.coverPhoto")
                ->description('wcf.user.coverPhoto.upload.description', [
                    'coverPhotoDimensions' => UserCoverPhoto::getCoverPhotoDimensions()
                ])
                ->singleFileUpload()
                ->bigPreview()
                ->simpleReplace()
                ->thumbnailSize('small')
        ]);

        $form->markRequiredFields(false);
        $form->updatedObject($user);
        $form->build();

        return $form;
    }
}
