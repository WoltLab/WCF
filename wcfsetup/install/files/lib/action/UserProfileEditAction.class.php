<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\user\UpdateUser;
use wcf\data\user\option\UserOption;
use wcf\data\user\UserBuilder;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\Psr15BuilderDialogForm;
use wcf\system\form\option\user\TUserOptionFormFields;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Edits the profile of a user in a dialog.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserProfileEditAction implements RequestHandlerInterface
{
    use TUserOptionFormFields {
        getUserOptionFormField as getDefaultUserOptionFormField;
    }

    private UserProfile $user;

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
        $this->assertProfileCanBeEdited($user);
        $this->user = $user;

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $builder = UserBuilder::forUpdate($this->user->getDecoratedObject());
            $form->applyValuesToBuilder($builder);
            new UpdateUser($builder)();

            return new JsonResponse([
                'result' => [],
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * @phpstan-assert UserProfile $user
     */
    private function assertProfileCanBeEdited(?UserProfile $user): void
    {
        if ($user === null || $user->isGuest()) {
            throw new IllegalLinkException();
        }

        if ($user->userID !== WCF::getUser()->userID) {
            if (!$user->canEdit()) {
                throw new PermissionDeniedException();
            }
        } elseif (!$user->canEditOwnProfile()) {
            throw new PermissionDeniedException();
        }
    }

    private function getForm(): Psr15BuilderDialogForm
    {
        $form = new Psr15BuilderDialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.user.editProfile')
        );

        if (\MODULE_USER_RANK !== 0 && WCF::getSession()->hasPermission('user.profile.canEditUserTitle')) {
            $form->appendChild(
                FormContainer::create('general')
                    ->appendChild($this->getUserTitleFormField())
            );
        }

        $form->appendChildren($this->getUserOptionFormContainers(
            $this->getUserOptionsByCategory('profile', function (UserOption $option) {
                if (!$option->validateOptions() || !$option->validatePermissions()) {
                    return false;
                }

                return $option->isEditable(user: $this->user->getDecoratedObject());
            })
        ));

        $form->updatedObject($this->user->getDecoratedObject());
        $form->build();

        return $form;
    }

    private function getUserTitleFormField(): TextFormField
    {
        return TextFormField::create('userTitle')
            ->label('wcf.user.userTitle')
            ->description('wcf.user.userTitle.description')
            ->maximumLength(\USER_TITLE_MAX_LENGTH)
            ->addValidator(new FormFieldValidator(
                'forbidden',
                static function (TextFormField $formField) {
                    $userTitle = StringUtil::trim((string)$formField->getValue());
                    if (!StringUtil::executeWordFilter($userTitle, \USER_FORBIDDEN_TITLES)) {
                        $formField->addValidationError(new FormFieldValidationError(
                            'forbidden',
                            'wcf.user.userTitle.error.forbidden'
                        ));
                    }
                }
            ))
            ->saveValueCallback(static function (UserBuilder $builder, TextFormField $formField) {
                $builder->setUserTitle(StringUtil::trim((string)$formField->getValue()));
            });
    }

    /**
     * Returns the form field for the given user option, administrators may
     * leave required options empty when editing other users.
     */
    private function getUserOptionFormField(UserOption $option): AbstractFormField
    {
        $formField = $this->getDefaultUserOptionFormField($option);

        if ($this->user->userID !== WCF::getUser()->userID) {
            $formField->required(false);
        }

        return $formField;
    }
}
