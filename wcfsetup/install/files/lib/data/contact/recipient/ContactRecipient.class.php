<?php

namespace wcf\data\contact\recipient;

use wcf\data\CollectionDatabaseObject;
use wcf\data\ITitledObject;
use wcf\system\email\Mailbox;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;

/**
 * Represents a contact recipient.
 *
 * The localized name and email address are stored in the
 * `wcf1_contact_recipient_l10n` table.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $recipientID        unique id of the recipient
 * @property-read   int     $showOrder          position of the recipient in relation to other recipients
 * @property-read   0|1     $isAdministrator    is `1` if the recipient is the administrator and the email address equals `MAIL_ADMIN_ADDRESS`, otherwise `0`
 * @property-read   0|1     $isDisabled         is `1` if the recipient is disabled and thus is not available for selection, otherwise `0`
 * @property-read   0|1     $originIsSystem     is `1` if the recipient has been delivered by a package, otherwise `0` (i.e. the recipient has been created in the ACP)
 * @property-read   ?string $l10nIdentifier     name of the language variable the localized name is derived from, `null` for recipients created by an administrator or renamed monolingually before 6.3
 *
 * @extends CollectionDatabaseObject<ContactRecipientCollection>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
class ContactRecipient extends CollectionDatabaseObject implements ITitledObject, \Stringable
{
    #[\Override]
    protected function handleData(array $data)
    {
        // `L10nContactRecipientList` reads the email address into the data,
        // which is empty for the administrator.
        if (($data['isAdministrator'] ?? 0) === 1) {
            $data['email'] = \MAIL_ADMIN_ADDRESS;
        }

        parent::handleData($data);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getName();
    }

    /**
     * Returns the localized name of this recipient.
     *
     * @since 5.3
     */
    public function getName(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'name');
    }

    /**
     * Returns the localized email address of this recipient.
     *
     * @since 5.3
     */
    public function getEmail(): string
    {
        if ($this->isAdministrator === 1) {
            return \MAIL_ADMIN_ADDRESS;
        }

        return $this->getCollection()->getResolvedL10nValue($this, 'email');
    }

    /**
     * Returns a localized Mailbox for this recipient.
     *
     * @since 5.3
     */
    public function getMailbox(): Mailbox
    {
        return new Mailbox(
            $this->getEmail(),
            $this->getName()
        );
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->getName();
    }

    /**
     * Returns the localized values of the given column as a
     * `languageID => value` map (see `L10nStorage`).
     *
     * @return L10nValue
     * @since 6.3
     */
    public function getL10nValues(string $columnName): array
    {
        if ($columnName !== 'name' && $columnName !== 'email') {
            throw new \InvalidArgumentException("Invalid column name given.");
        }

        return $this->getCollection()->getL10nValues($this, $columnName);
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_contact_recipient',
            'wcf1_contact_recipient_l10n',
            'recipientID',
            ['name', 'email'],
            'l10nIdentifier',
            // No language variable exists for the email address, the
            // administrator's address is `MAIL_ADMIN_ADDRESS`.
            ['name' => '', 'email' => '.email'],
            ['name' => 255, 'email' => 255],
        );
    }
}
