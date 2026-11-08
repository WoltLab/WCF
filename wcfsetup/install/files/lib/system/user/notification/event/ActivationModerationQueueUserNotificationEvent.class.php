<?php

namespace wcf\system\user\notification\event;

use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\form\ModerationActivationForm;
use wcf\system\email\Email;
use wcf\system\moderation\queue\IModerationQueueHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\ModerationQueueUserNotificationObject;
use wcf\system\WCF;

/**
 * Notification event for new content awaiting approval in the moderation queue.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @method  ModerationQueueUserNotificationObject    getUserNotificationObject()
 */
final class ActivationModerationQueueUserNotificationEvent extends AbstractUserNotificationEvent implements
    IRecipientAwareUserNotificationEvent,
    ITestableUserNotificationEvent
{
    use TTestableModerationQueueUserNotificationEvent;
    use TTestableUserNotificationEvent;

    private ?ViewableModerationQueue $viewableModerationQueue;
    private User $recipient;

    #[\Override]
    public function getTitle(): string
    {
        return $this->getLanguage()->get('wcf.moderation.activation.notification.title');
    }

    #[\Override]
    public function getMessage()
    {
        return $this->getLanguage()->getDynamicVariable(
            'wcf.moderation.activation.notification.message',
            $this->getLanguageVariables()
        );
    }

    #[\Override]
    public function getEmailMessage(string $notificationType = 'instant'): array
    {
        $affectedObject = $this->getViewableModerationQueue()->getAffectedObject();

        return [
            'message-id' => 'com.woltlab.wcf.moderation.queue.notification/'
                . $this->getUserNotificationObject()->queueID,
            'template' => 'email_notification_moderationQueueActivation',
            'application' => 'wcf',
            'references' => [
                '<com.woltlab.wcf.moderation.queue/'
                    . $this->getUserNotificationObject()->queueID . '@' . Email::getHost() . '>',
            ],
            'variables' => [
                ...$this->getLanguageVariables(),
                'affectedObject' => \method_exists($affectedObject, 'getMailText') ? $affectedObject : null,
            ],
        ];
    }

    #[\Override]
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getControllerLink(ModerationActivationForm::class, [
            'id' => $this->getUserNotificationObject()->queueID,
        ]);
    }

    #[\Override]
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getUserNotificationObject()->queueID);
    }

    #[\Override]
    public function checkAccess()
    {
        if ($this->getViewableModerationQueue() === null) {
            return false;
        }
        $objectType = ObjectTypeCache::getInstance()->getObjectType($this->getUserNotificationObject()->objectTypeID);
        $processor = $objectType->getProcessor();
        \assert($processor instanceof IModerationQueueHandler);

        return $processor->isAffectedUser(
            $this->getUserNotificationObject()->getDecoratedObject(),
            $this->getRecipient()->userID
        );
    }

    #[\Override]
    public function setRecipient(User $user): void
    {
        $this->recipient = $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function getLanguageVariables(): array
    {
        return [
            'author' => $this->author,
            'notification' => $this->notification,
            'moderationQueue' => $this->getViewableModerationQueue(),
            'title' => $this->getViewableModerationQueue()->getTitle(),
            'objectLink' => $this->getViewableModerationQueue()->getLink(),
            'typeName' => $this->getLanguage()->getDynamicVariable(
                "wcf.moderation.type." . $this->getViewableModerationQueue()->getObjectTypeName()
            ),
        ];
    }

    private function getRecipient(): User
    {
        return $this->recipient ?? WCF::getUser();
    }

    private function getViewableModerationQueue(): ?ViewableModerationQueue
    {
        if (!isset($this->viewableModerationQueue)) {
            $this->viewableModerationQueue = ViewableModerationQueue::getViewableModerationQueue(
                $this->getUserNotificationObject()->queueID,
                $this->getRecipient(),
            );
        }
        return $this->viewableModerationQueue;
    }

    #[\Override]
    public static function canBeTriggeredByGuests()
    {
        return true;
    }

    #[\Override]
    public static function getTestObjects(UserProfile $recipient, UserProfile $author)
    {
        return [
            new ModerationQueueUserNotificationObject(self::getTestActivationModerationQueueEntry($recipient, $author)),
        ];
    }
}
