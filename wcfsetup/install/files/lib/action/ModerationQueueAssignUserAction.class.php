<?php

namespace wcf\action;

use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\RadioButtonFormField;
use wcf\system\form\builder\field\user\UserFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\field\validation\FormFieldValidator;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\command\moderation\queue\AssignUser;
use wcf\system\WCF;

/**
 * Assigns a user to a moderation queue entry.
 *
 * @author      Olaf Braun, Tim Duesterhus
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 *
 * @phpstan-import-type PerformActionResult from AbstractModerationAction
 */
final class ModerationQueueAssignUserAction extends AbstractModerationAction
{
    private readonly ObjectTypeCache $objectTypeCache;

    public function __construct()
    {
        $this->objectTypeCache = ObjectTypeCache::getInstance();
    }

    #[\Override]
    protected function getForm(array $moderationQueues): Psr15DialogForm
    {
        // The current user should not appear in the
        // "other user" selection if they are assigned.
        $assignedUserID = 0;
        $moderationQueue = \count($moderationQueues) === 1 ? \reset($moderationQueues) : null;
        if ($moderationQueue?->assignedUserID && $moderationQueue->assignedUserID !== WCF::getUser()->userID) {
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
                    match ($moderationQueue?->assignedUserID) {
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
                ->addValidator(
                    new FormFieldValidator(
                        'isAffected',
                        function (UserFormField $formField) use ($moderationQueues) {
                            $user = User::getUserByUsername($formField->getValue());

                            foreach ($moderationQueues as $moderationQueue) {
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
                            }
                        }
                    )
                ),
        ]);

        $form->markRequiredFields(false);

        $form->build();

        return $form;
    }

    /**
     * @return PerformActionResult
     */
    #[\Override]
    protected function performAction(ModerationQueue $queue, Psr15DialogForm $form): array
    {
        $data = $form->getData()['data'];

        $user = match ($data['assignee']) {
            'none' => null,
            'me' => WCF::getUser(),
            'other' => new User($data['other']),
        };

        $command = new AssignUser(
            $queue,
            $user
        );
        $command();

        // Reload the moderation queue to fetch the new status.
        $queue = new ModerationQueue($queue->queueID);

        $assignee = null;
        if ($user !== null) {
            $assignee = [
                'username' => $user->username,
                'userID' => $user->userID,
                'link' => $user->getLink(),
            ];
        }

        return [
            'result' => [
                'assignee' => $assignee,
                'status' => $queue->getStatus(),
            ],
        ];
    }
}
