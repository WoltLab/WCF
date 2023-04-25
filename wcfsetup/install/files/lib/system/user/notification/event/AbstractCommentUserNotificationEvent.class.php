<?php

namespace wcf\system\user\notification\event;

/**
 * Provides a default implementation for user notifications about comments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
abstract class AbstractCommentUserNotificationEvent extends AbstractSharedUserNotificationEvent
{
    /**
     * @inheritDoc
     */
    protected $stackable = true;

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        $count = \count($this->getAuthors());
        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.title.stacked', [
                'count' => $count,
                'timesTriggered' => $this->notification->timesTriggered,
                'typeName' => $this->getTypeName(),
            ]);
        }

        return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.title', [
            'typeName' => $this->getTypeName(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getEmailTitle()
    {
        $count = \count($this->getAuthors());
        if ($count > 1) {
            return $this->getTitle();
        }

        return $this->getLanguage()->getDynamicVariable('wcf.user.notification.comment.mail.title', [
            'objectTitle' => $this->getObjectTitle(),
            'typeName' => $this->getTypeName(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getUserNotificationObject()->objectID);
    }

    /**
     * Returns the name of the type to which the comment belong.
     */
    protected abstract function getTypeName(): string;

    /**
     * Returns the title of the object to which the comment belong.
     */
    protected abstract function getObjectTitle(): string;
}
