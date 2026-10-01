<?php

namespace wcf\data\contact\recipient;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\system\l10n\L10nStorage;

/**
 * Builder for creating and updating contact recipients.
 *
 * The localized name and email address are stored in the
 * `wcf1_contact_recipient_l10n` table (see `L10nStorage`). `l10nIdentifier`
 * links the recipient shipped with the package to its language variable; it
 * is `NULL` for recipients created by an administrator.
 *
 * The administrator recipient has no email address of its own, it always uses
 * `MAIL_ADMIN_ADDRESS`.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<ContactRecipient>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class ContactRecipientBuilder extends DatabaseObjectBuilder
{
    /**
     * @var L10nValue
     */
    private array $name;

    /**
     * @var L10nValue
     */
    private array $email;

    /**
     * @param L10nValue $name
     */
    public function setName(array $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param L10nValue $email
     */
    public function setEmail(array $email): static
    {
        if ($this->isAdministratorRecipient()) {
            throw new \BadMethodCallException("The email address of the administrator recipient cannot be changed.");
        }

        $this->email = $email;

        return $this;
    }

    public function setL10nIdentifier(?string $l10nIdentifier): static
    {
        $this->properties['l10nIdentifier'] = $l10nIdentifier;

        return $this;
    }

    public function setShowOrder(int $showOrder): static
    {
        $this->properties['showOrder'] = $showOrder;

        return $this;
    }

    public function setIsDisabled(bool $isDisabled): static
    {
        $this->properties['isDisabled'] = $isDisabled ? 1 : 0;

        return $this;
    }

    #[\Override]
    protected function allowEmptyCreate(): bool
    {
        return true;
    }

    #[\Override]
    protected function afterValidateCreate(): void
    {
        // A shipped recipient receives its values from the language variable
        // through `SyncL10nLanguageItems`.
        if (
            !isset($this->name)
            && !isset($this->email)
            && ($this->properties['l10nIdentifier'] ?? null) !== null
        ) {
            return;
        }

        if (!isset($this->name)) {
            throw new \BadMethodCallException("Missing value for 'name'.");
        }
        if (!isset($this->email)) {
            throw new \BadMethodCallException("Missing value for 'email'.");
        }
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        if (isset($this->name) || isset($this->email)) {
            $this->saveL10nValues($object);
        }
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        if (isset($this->name) || isset($this->email)) {
            $this->saveL10nValues($object);
        }
    }

    private function isAdministratorRecipient(): bool
    {
        return $this->isUpdate() && $this->getObject()->isAdministrator === 1;
    }

    private function saveL10nValues(ContactRecipient $recipient): void
    {
        if ($this->isAdministratorRecipient()) {
            // The administrator recipient has no email address of its own.
            $this->email = \array_fill_keys(\array_keys($this->name), '');
        }

        // `L10nStorage::setValues()` replaces all rows of the recipient.
        (new L10nStorage(ContactRecipient::getL10nDefinition()))->setValues(
            $recipient->recipientID,
            [
                'name' => $this->name ?? $this->getObject()->getL10nValues('name'),
                'email' => $this->email ?? $this->getObject()->getL10nValues('email'),
            ]
        );
    }
}
