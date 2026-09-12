<?php

namespace wcf\data\email\log\entry;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\User;
use wcf\system\email\IUserMailbox;
use wcf\system\email\Mailbox;
use wcf\system\WCF;

/**
 * Builder for creating, updating and deleting email log entries.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<EmailLogEntry>
 */
final class EmailLogEntryBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the timestamp at which the delivery job has been created.
     */
    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    /**
     * Sets the email's subject.
     */
    public function setSubject(string $subject): static
    {
        $this->properties['subject'] = \mb_substr($subject, 0, 255);

        return $this;
    }

    /**
     * Sets the email's 'Message-ID'.
     */
    public function setMessageID(string $messageID): static
    {
        $this->properties['messageID'] = \mb_substr($messageID, 0, 255);

        return $this;
    }

    /**
     * Sets the recipient of the email, including the recipient's user id if the
     * email is being sent to a registered user.
     */
    public function setRecipient(Mailbox $mailbox): static
    {
        $this->setRecipientAddress($mailbox->getAddress());

        return $this->setRecipientID(
            $mailbox instanceof IUserMailbox ? $mailbox->getUser()->userID : null
        );
    }

    /**
     * Sets the recipient's email address ("RCPT TO").
     */
    public function setRecipientAddress(string $recipient): static
    {
        $this->properties['recipient'] = \mb_substr($recipient, 0, 255);

        return $this;
    }

    /**
     * Sets the recipient, if the email is being sent to a registered user.
     */
    public function setRecipientUser(?User $user): static
    {
        return $this->setRecipientID($user?->userID);
    }

    /**
     * Sets the recipient's user id, if the email is being sent to a registered user.
     */
    public function setRecipientID(?int $recipientID): static
    {
        $this->properties['recipientID'] = $recipientID;

        return $this;
    }

    /**
     * Sets the delivery status, must be one of the `EmailLogEntry::STATUS_*` constants.
     */
    public function setStatus(string $status): static
    {
        $this->properties['status'] = $status;

        return $this;
    }

    /**
     * Sets a human readable explanation for the status.
     */
    public function setMessage(?string $message): static
    {
        $this->properties['message'] = $message;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['time', 'messageID', 'subject', 'recipient', 'status'];
    }

    /**
     * Deletes all entries that have been created before the given timestamp.
     */
    public static function deleteCreatedBefore(int $timestamp): void
    {
        $sql = "DELETE FROM " . EmailLogEntry::getDatabaseTableName() . "
                WHERE       time < ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$timestamp]);
    }
}
