<?php

namespace wcf\data\user;

use ParagonIE\ConstantTime\Hex;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\group\UserGroup;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\user\authentication\password\algorithm\Invalid as InvalidPasswordAlgorithm;
use wcf\system\user\authentication\password\PasswordAlgorithmManager;
use wcf\system\WCF;

/**
 * Builder for creating, updating and deleting users.
 *
 * The user options, the user groups and the content languages are stored in
 * separate tables that are written after the user itself. The object returned
 * by `create()` and `update()` is loaded before these tables are written, use
 * the `CreateUser` and `UpdateUser` commands to obtain an up-to-date object.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<User>
 */
final class UserBuilder extends DatabaseObjectBuilder
{
    /**
     * values of the user options by option id, `null` if the user options are not changed
     * @var ?array<int, ?string>
     */
    private ?array $userOptions = null;

    /**
     * ids of the user groups, `null` if the user groups are not changed
     * @var ?list<int>
     */
    private ?array $groupIDs = null;

    /**
     * ids of the content languages, `null` if the content languages are not changed
     * @var ?list<int>
     */
    private ?array $languageIDs = null;

    private bool $passwordChanged = false;

    private HtmlInputProcessor $signatureHtmlInputProcessor;

    private AttachmentHandler $signatureAttachmentHandler;

    public function setUsername(string $username): static
    {
        $this->properties['username'] = $username;

        return $this;
    }

    public function setEmail(string $email): static
    {
        $this->properties['email'] = $email;

        return $this;
    }

    /**
     * Sets the password of the user, which is stored as a hash. Passing `null`
     * sets an invalid password that prevents logging in with a password.
     *
     * Changing the password also changes the access token of the user.
     */
    public function setPassword(
        #[\SensitiveParameter]
        ?string $password
    ): static {
        $this->properties['password'] = self::getPasswordHash($password);
        $this->properties['accessToken'] = Hex::encode(\random_bytes(20));
        $this->passwordChanged = true;

        return $this;
    }

    public function setLanguageID(int $languageID): static
    {
        $this->properties['languageID'] = $languageID;

        return $this;
    }

    /**
     * Sets the id of the style chosen by the user, `0` uses the default style.
     */
    public function setStyleID(int $styleID): static
    {
        $this->properties['styleID'] = $styleID;

        return $this;
    }

    public function setRegistrationDate(int $registrationDate): static
    {
        $this->properties['registrationDate'] = $registrationDate;

        return $this;
    }

    public function setRegistrationIpAddress(string $registrationIpAddress): static
    {
        $this->properties['registrationIpAddress'] = $registrationIpAddress;

        return $this;
    }

    /**
     * Sets the activation code, `0` marks the user as activated.
     */
    public function setActivationCode(int $activationCode): static
    {
        $this->properties['activationCode'] = $activationCode;

        return $this;
    }

    /**
     * Sets the code to confirm the email address, `null` marks the email
     * address as confirmed.
     */
    public function setEmailConfirmed(?string $emailConfirmed): static
    {
        $this->properties['emailConfirmed'] = $emailConfirmed;

        return $this;
    }

    public function setBanned(bool $banned): static
    {
        $this->properties['banned'] = (int)$banned;

        return $this;
    }

    public function setBanReason(?string $banReason): static
    {
        $this->properties['banReason'] = $banReason;

        return $this;
    }

    /**
     * Sets the timestamp at which the ban expires, `0` bans the user permanently.
     */
    public function setBanExpires(int $banExpires): static
    {
        $this->properties['banExpires'] = $banExpires;

        return $this;
    }

    public function setDisableAvatar(bool $disableAvatar): static
    {
        $this->properties['disableAvatar'] = (int)$disableAvatar;

        return $this;
    }

    public function setDisableAvatarReason(?string $disableAvatarReason): static
    {
        $this->properties['disableAvatarReason'] = $disableAvatarReason;

        return $this;
    }

    /**
     * Sets the timestamp at which the avatar is enabled again, `0` disables
     * the avatar permanently.
     */
    public function setDisableAvatarExpires(int $disableAvatarExpires): static
    {
        $this->properties['disableAvatarExpires'] = $disableAvatarExpires;

        return $this;
    }

    public function setDisableCoverPhoto(bool $disableCoverPhoto): static
    {
        $this->properties['disableCoverPhoto'] = (int)$disableCoverPhoto;

        return $this;
    }

    public function setDisableCoverPhotoReason(?string $disableCoverPhotoReason): static
    {
        $this->properties['disableCoverPhotoReason'] = $disableCoverPhotoReason;

        return $this;
    }

    /**
     * Sets the timestamp at which the cover photo is enabled again, `0`
     * disables the cover photo permanently.
     */
    public function setDisableCoverPhotoExpires(int $disableCoverPhotoExpires): static
    {
        $this->properties['disableCoverPhotoExpires'] = $disableCoverPhotoExpires;

        return $this;
    }

    public function setSignature(?string $signature): static
    {
        $this->properties['signature'] = $signature;

        return $this;
    }

    public function setSignatureEnableHtml(bool $signatureEnableHtml): static
    {
        $this->properties['signatureEnableHtml'] = (int)$signatureEnableHtml;

        return $this;
    }

    /**
     * Sets the signature from the given processor, the embedded objects are
     * registered after the user has been saved.
     */
    public function setSignatureHtmlInputProcessor(HtmlInputProcessor $htmlInputProcessor): static
    {
        $this->signatureHtmlInputProcessor = $htmlInputProcessor;
        $this->setSignature($htmlInputProcessor->getHtml());

        return $this;
    }

    /**
     * Sets the handler of the signature attachments, which are assigned to the
     * user after it has been created.
     */
    public function setSignatureAttachmentHandler(AttachmentHandler $attachmentHandler): static
    {
        $this->signatureAttachmentHandler = $attachmentHandler;

        return $this;
    }

    public function setDisableSignature(bool $disableSignature): static
    {
        $this->properties['disableSignature'] = (int)$disableSignature;

        return $this;
    }

    public function setDisableSignatureReason(?string $disableSignatureReason): static
    {
        $this->properties['disableSignatureReason'] = $disableSignatureReason;

        return $this;
    }

    public function setDisableSignatureExpires(int $disableSignatureExpires): static
    {
        $this->properties['disableSignatureExpires'] = $disableSignatureExpires;

        return $this;
    }

    public function setUserTitle(string $userTitle): static
    {
        $this->properties['userTitle'] = $userTitle;

        return $this;
    }

    public function setLastUsernameChange(int $lastUsernameChange): static
    {
        $this->properties['lastUsernameChange'] = $lastUsernameChange;

        return $this;
    }

    public function setOldUsername(string $oldUsername): static
    {
        $this->properties['oldUsername'] = $oldUsername;

        return $this;
    }

    public function setAuthData(string $authData): static
    {
        $this->properties['authData'] = $authData;

        return $this;
    }

    /**
     * Sets the hash of the key that allows the user to set a new password,
     * `null` invalidates a pending request.
     */
    public function setLostPasswordKey(?string $lostPasswordKeyHash): static
    {
        $this->properties['lostPasswordKey'] = $lostPasswordKeyHash;

        return $this;
    }

    public function setLastLostPasswordRequestTime(int $lastLostPasswordRequestTime): static
    {
        $this->properties['lastLostPasswordRequestTime'] = $lastLostPasswordRequestTime;

        return $this;
    }

    /**
     * Sets the value of a user option, see `UserOption::getFormOption()` for
     * serializing form values.
     */
    public function setUserOption(int $optionID, ?string $value): static
    {
        $this->userOptions ??= [];
        $this->userOptions[$optionID] = $value;

        return $this;
    }

    /**
     * Sets the user groups of the user, replacing the existing ones. New users
     * are added to the default groups `EVERYONE` and `USERS` unless the user
     * groups are set explicitly.
     *
     * @param list<int> $groupIDs
     */
    public function setGroupIDs(array $groupIDs): static
    {
        $this->groupIDs = $groupIDs;

        return $this;
    }

    /**
     * Sets the content languages of the user, replacing the existing ones. An
     * empty list falls back to the default language.
     *
     * @param list<int> $languageIDs
     */
    public function setLanguageIDs(array $languageIDs): static
    {
        $this->languageIDs = $languageIDs;

        return $this;
    }

    /**
     * @return ?array<int, ?string>
     */
    public function getUserOptions(): ?array
    {
        return $this->userOptions;
    }

    /**
     * @return ?list<int>
     */
    public function getGroupIDs(): ?array
    {
        return $this->groupIDs;
    }

    /**
     * @return ?list<int>
     */
    public function getLanguageIDs(): ?array
    {
        return $this->languageIDs;
    }

    public function isPasswordChanged(): bool
    {
        return $this->passwordChanged;
    }

    /**
     * Sets the matches of the spam check during the registration.
     *
     * @param string[] $blacklistMatches
     */
    public function setBlacklistMatches(array $blacklistMatches): static
    {
        $this->properties['blacklistMatches'] = $blacklistMatches !== []
            ? \json_encode($blacklistMatches, \JSON_THROW_ON_ERROR)
            : '';

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['username', 'email', 'password', 'registrationDate'];
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        $this->createUserOptions($object);
        $this->saveGroupIDs(
            $object,
            $this->groupIDs ?? UserGroup::getGroupIDsByType([UserGroup::EVERYONE, UserGroup::USERS]),
            false
        );
        $this->saveLanguageIDs($object, $this->languageIDs ?? [], false);

        if (isset($this->signatureHtmlInputProcessor)) {
            $this->registerSignatureEmbeddedObjects($object);
        }

        if (isset($this->signatureAttachmentHandler)) {
            $this->signatureAttachmentHandler->updateObjectID($object->userID);
        }

        if (\PACKAGE_ID !== 0) {
            // set default notifications
            $sql = "INSERT INTO wcf1_user_notification_event_to_user
                                (userID, eventID, mailNotificationType)
                    SELECT      ?, eventID, presetMailNotificationType
                    FROM        wcf1_user_notification_event
                    WHERE       preset = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$object->userID, 1]);
        }
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        if ($this->userOptions !== null) {
            $this->updateUserOptions($object);
        }

        if ($this->groupIDs !== null) {
            $this->saveGroupIDs($object, $this->groupIDs, true);
        }

        if ($this->languageIDs !== null) {
            $this->saveLanguageIDs($object, $this->languageIDs, true);
        }

        if (isset($this->signatureHtmlInputProcessor)) {
            $this->registerSignatureEmbeddedObjects($object);
        }
    }

    private function registerSignatureEmbeddedObjects(User $user): void
    {
        $this->signatureHtmlInputProcessor->setObjectID($user->userID);
        MessageEmbeddedObjectManager::getInstance()->registerObjects($this->signatureHtmlInputProcessor);
    }

    /**
     * Inserts the row with the user option values, using the default value of
     * the options that have not been set explicitly.
     */
    private function createUserOptions(User $user): void
    {
        $sql = "SELECT  optionID, defaultValue
                FROM    wcf1_user_option
                WHERE   defaultValue IS NOT NULL";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $values = $statement->fetchMap('optionID', 'defaultValue');

        foreach ($this->userOptions ?? [] as $optionID => $value) {
            if ($value === null) {
                // Omit the column to fall back to its default value.
                unset($values[$optionID]);
            } else {
                $values[$optionID] = $value;
            }
        }

        $keys = $placeholders = '';
        $statementParameters = [$user->userID];
        foreach ($values as $optionID => $value) {
            $keys .= ', userOption' . $optionID;
            $placeholders .= ', ?';
            $statementParameters[] = $value;
        }

        $sql = "INSERT INTO wcf1_user_option_value
                            (userID" . $keys . ")
                VALUES      (?" . $placeholders . ")";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($statementParameters);
    }

    private function updateUserOptions(User $user): void
    {
        $columns = [];
        $statementParameters = [];
        foreach ($this->userOptions as $optionID => $value) {
            $columns[] = 'userOption' . $optionID . ' = ?';
            $statementParameters[] = $value;
        }
        $statementParameters[] = $user->userID;

        $sql = "UPDATE  wcf1_user_option_value
                SET     " . \implode(', ', $columns) . "
                WHERE   userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($statementParameters);
    }

    /**
     * @param list<int> $groupIDs
     */
    private function saveGroupIDs(User $user, array $groupIDs, bool $replace): void
    {
        if ($replace) {
            $sql = "DELETE FROM wcf1_user_to_group
                    WHERE       userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$user->userID]);
        }

        $sql = "INSERT IGNORE INTO  wcf1_user_to_group
                                    (userID, groupID)
                VALUES              (?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($groupIDs as $groupID) {
            $statement->execute([$user->userID, $groupID]);
        }
    }

    /**
     * Saves the content languages of the user, an empty list falls back to the
     * default language.
     *
     * @param list<int> $languageIDs
     */
    private function saveLanguageIDs(User $user, array $languageIDs, bool $replace): void
    {
        if ($replace) {
            $sql = "DELETE FROM wcf1_user_to_language
                    WHERE       userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$user->userID]);
        }

        if ($languageIDs === []) {
            $languageIDs = [LanguageFactory::getInstance()->getDefaultLanguageID()];
        }

        $sql = "INSERT IGNORE INTO  wcf1_user_to_language
                                    (userID, languageID)
                VALUES              (?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($languageIDs as $languageID) {
            $statement->execute([$user->userID, $languageID]);
        }
    }

    private static function getPasswordHash(
        #[\SensitiveParameter]
        ?string $password
    ): string {
        $manager = PasswordAlgorithmManager::getInstance();

        $algorithm = $manager->getDefaultAlgorithm();
        if ($password === null) {
            $algorithm = new InvalidPasswordAlgorithm();
            $password = '';
        }

        return $manager->getNameFromAlgorithm($algorithm) . ':' . $algorithm->hash($password);
    }
}
