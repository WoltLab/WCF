<?php

namespace wcf\action;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\user\UserFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\moderation\queue\command\AssignUser;
use wcf\system\WCF;

/**
 * Assigns a user to a moderation queue entry.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class ModerationQueueAssignUserAction implements RequestHandlerInterface
{
    private const PARAMETERS = <<<'EOT'
        array {
            id: positive-int
        }
        EOT;

    private TreeMapper $mapper;

    private readonly ObjectTypeCache $objectTypeCache;

    public function __construct()
    {
        $this->objectTypeCache = ObjectTypeCache::getInstance();

        $this->mapper = (new MapperBuilder())
            ->allowSuperfluousKeys()
            ->enableFlexibleCasting()
            ->mapper();
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = $this->mapper->map(
            self::PARAMETERS,
            Source::array($request->getQueryParams())
        );

        $moderationQueue = new ModerationQueue($parameters['id']);
        if (!$moderationQueue->queueID) {
            throw new IllegalLinkException();
        }

        if (!$moderationQueue->canEdit(WCF::getUser())) {
            throw new PermissionDeniedException();
        }

        $form = $this->getForm($moderationQueue);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];

            $user = match ($data['assignee']) {
                'none' => null,
                'me' => WCF::getUser(),
                'other' => new User($data['other']),
            };

            $command = new AssignUser(
                $moderationQueue,
                $user
            );
            $command();

            // Reload the moderation queue to fetch the new status.
            $moderationQueue = new ModerationQueue($moderationQueue->queueID);

            $assignee = null;
            if ($user !== null) {
                $assignee = [
                    'username' => $user->username,
                    'userID' => $user->userID,
                    'link' => $user->getLink(),
                ];
            }

            return new JsonResponse([
                'result' => [
                    'assignee' => $assignee,
                    'status' => $moderationQueue->getStatus(),
                ],
            ]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(ModerationQueue $moderationQueue): Psr15DialogForm
    {
        // The current user should not appear in the
        // "other user" selection if they are assigned.
        $assignedUserID = 0;
        if ($moderationQueue->assignedUserID && $moderationQueue->assignedUserID !== WCF::getUser()->userID) {
            $assignedUserID = $moderationQueue->assignedUserID;
        }

        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.moderation.assignedUser.change')
        );
        $form->appendChildren([
            RadioButtonFormField::create('assignee')
                ->required()
                ->options([
                    'none' => WCF::getLanguage()->get('wcf.moderation.assignedUser.nobody'),
                    'me' => WCF::getUser()->username,
                    'other' => WCF::getLanguage()->get('wcf.moderation.assignedUser.other'),
                ])
                ->value(
                    match ($moderationQueue->assignedUserID) {
                        WCF::getUser()->userID => 'me',
                        null => 'none',
                        default => 'other'
                    }
                ),
            UserFormField::create('other')
                ->addDependency(
                    ValueFormFieldDependency::create('other')
                        ->fieldId('assignee')
                        ->values(['other'])
                )
                ->value(
                    $assignedUserID ?: []
                )
                ->label('wcf.user.username')
                ->required()
                ->addValidator(new FormFieldValidator('isAffected', function (UserFormField $formField) use ($moderationQueue) {
                    $user = User::getUserByUsername($formField->getValue());

                    $objectType = $this->objectTypeCache->getObjectType($moderationQueue->objectTypeID);
                    if (
                        !$objectType->getProcessor()->isAffectedUser(
                            $moderationQueue,
                            $user->userID
                        )
                    ) {
                        $formField->addValidationError(
                            new FormFieldValidationError(
                                'notAffected',
                                'wcf.moderation.assignedUser.error.notAffected'
                            )
                        );
                    }
                })),
        ]);

        $form->markRequiredFields(false);

        $form->build();

        return $form;
    }
}
