<?php

namespace wcf\data\user;

use ParagonIE\ConstantTime\Hex;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\data\user\group\UserGroup;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\user\authentication\password\algorithm\Invalid as InvalidPasswordAlgorithm;
use wcf\system\user\authentication\password\PasswordAlgorithmManager;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Provides functions to edit users.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  User    getDecoratedObject()
 * @mixin   User
 */
class UserEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = User::class;

    /**
     * list of user options default values
     * @var array
     */
    protected static $userOptionDefaultValues;

    /**
     * Returns the encoded password hash + algorithm for the given password.
     *
     * A `null` password will result in the `Invalid` algorithm, otherwise
     * the default algorithm will be used.
     *
     * @since 5.4
     */
    private static function getPasswordHash(
        #[\SensitiveParameter]
        ?string $password = null
    ): string {
        $manager = PasswordAlgorithmManager::getInstance();

        $algorithm = $manager->getDefaultAlgorithm();
        if ($password === null) {
            $algorithm = new InvalidPasswordAlgorithm();
            $password = '';
        }

        return $manager->getNameFromAlgorithm($algorithm) . ':' . $algorithm->hash($password);
    }

    /**
     * @inheritDoc
     * @return  User
     */
    public static function create(array $parameters = [])
    {
        if ($parameters['password'] !== '') {
            $parameters['password'] = self::getPasswordHash($parameters['password']);
        }

        // create accessToken for AbstractAuthedPage
        $parameters['accessToken'] = Hex::encode(\random_bytes(20));

        // handle registration date
        if (!isset($parameters['registrationDate'])) {
            $parameters['registrationDate'] = TIME_NOW;
        }

        /** @var User $user */
        $user = parent::create($parameters);

        // create default values for user options
        self::createUserOptions($user->userID);

        return $user;
    }

    /**
     * @inheritDoc
     */
    public static function deleteAll(array $objectIDs = [])
    {
        // unmark users
        ClipboardHandler::getInstance()->unmark(
            $objectIDs,
            ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user')
        );

        return parent::deleteAll($objectIDs);
    }

    /**
     * @inheritDoc
     */
    public function update(array $parameters = [])
    {
        if (\array_key_exists('password', $parameters) && $parameters['password'] !== '') {
            $parameters['password'] = self::getPasswordHash($parameters['password']);
            $parameters['accessToken'] = Hex::encode(\random_bytes(20));
        } else {
            unset($parameters['password'], $parameters['accessToken']);
        }

        parent::update($parameters);
    }

    /**
     * Inserts default options.
     */
    protected static function createUserOptions(int $userID)
    {
        // fetch default values
        if (self::$userOptionDefaultValues === null) {
            self::$userOptionDefaultValues = [];

            $sql = "SELECT  optionID, defaultValue
                    FROM    wcf1_user_option
                    WHERE   defaultValue IS NOT NULL";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute();
            while ($row = $statement->fetchArray()) {
                self::$userOptionDefaultValues[$row['optionID']] = $row['defaultValue'];
            }
        }

        // insert default values
        $keys = $values = '';
        $statementParameters = [$userID];
        foreach (self::$userOptionDefaultValues as $optionID => $optionValue) {
            $keys .= ', userOption' . $optionID;
            $values .= ', ?';
            $statementParameters[] = $optionValue;
        }

        $sql = "INSERT INTO wcf1_user_option_value
                            (userID" . $keys . ")
                VALUES      (?" . $values . ")";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($statementParameters);
    }

    /**
     * Updates user options.
     *
     * @param array $userOptions
     */
    public function updateUserOptions(array $userOptions = []): void
    {
        $updateSQL = '';
        $statementParameters = [];
        foreach ($userOptions as $optionID => $optionValue) {
            if (!empty($updateSQL)) {
                $updateSQL .= ',';
            }

            $updateSQL .= 'userOption' . $optionID . ' = ?';
            $statementParameters[] = $optionValue;
        }
        $statementParameters[] = $this->userID;

        if (!empty($updateSQL)) {
            $sql = "UPDATE  wcf1_user_option_value
                    SET     " . $updateSQL . "
                    WHERE   userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute($statementParameters);
        }
    }

    /**
     * Adds a user to the groups he should be in.
     *
     * @param int[] $groupIDs
     */
    public function addToGroups(array $groupIDs, bool $deleteOldGroups = true, bool $addDefaultGroups = true): void
    {
        // add default groups
        if ($addDefaultGroups) {
            $groupIDs = \array_merge($groupIDs, UserGroup::getGroupIDsByType([UserGroup::EVERYONE, UserGroup::USERS]));
            $groupIDs = \array_unique($groupIDs);
        }

        // remove old groups
        if ($deleteOldGroups) {
            $sql = "DELETE FROM wcf1_user_to_group
                    WHERE       userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->userID]);
        }

        // insert new groups
        if (!empty($groupIDs)) {
            $sql = "INSERT IGNORE INTO  wcf1_user_to_group
                                        (userID, groupID)
                    VALUES              (?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            foreach ($groupIDs as $groupID) {
                $statement->execute([$this->userID, $groupID]);
            }
        }
    }

    /**
     * Adds a user to a user group.
     */
    public function addToGroup(int $groupID): void
    {
        $sql = "INSERT IGNORE INTO  wcf1_user_to_group
                                    (userID, groupID)
                VALUES              (?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->userID, $groupID]);
    }

    /**
     * Removes a user from a user group.
     */
    public function removeFromGroup(int $groupID): void
    {
        $sql = "DELETE FROM wcf1_user_to_group
                WHERE       userID = ?
                        AND groupID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->userID, $groupID]);
    }

    /**
     * Removes a user from multiple user groups.
     *
     * @param int[] $groupIDs
     */
    public function removeFromGroups(array $groupIDs): void
    {
        $sql = "DELETE FROM wcf1_user_to_group
                WHERE       userID = ?
                        AND groupID = ?";
        $statement = WCF::getDB()->prepare($sql);
        foreach ($groupIDs as $groupID) {
            $statement->execute([
                $this->userID,
                $groupID,
            ]);
        }
    }

    /**
     * Saves the visible languages of a user.
     *
     * @param int[] $languageIDs
     */
    public function addToLanguages(array $languageIDs, bool $deleteOldLanguages = true): void
    {
        // remove previous languages
        if ($deleteOldLanguages) {
            $sql = "DELETE FROM wcf1_user_to_language
                    WHERE       userID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$this->userID]);
        }

        // insert language ids
        $sql = "INSERT IGNORE INTO  wcf1_user_to_language
                                    (userID, languageID)
                VALUES              (?, ?)";
        $statement = WCF::getDB()->prepare($sql);

        if (!empty($languageIDs)) {
            WCF::getDB()->beginTransaction();
            foreach ($languageIDs as $languageID) {
                $statement->execute([
                    $this->userID,
                    $languageID,
                ]);
            }
            WCF::getDB()->commitTransaction();
        } else {
            // no language id given, use default language id instead
            $statement->execute([
                $this->userID,
                LanguageFactory::getInstance()->getDefaultLanguageID(),
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public static function resetCache(): void
    {
        UserStorageHandler::getInstance()->resetAll('groupIDs');
        UserStorageHandler::getInstance()->resetAll('languageIDs');
    }
}
