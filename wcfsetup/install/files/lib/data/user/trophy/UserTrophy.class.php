<?php

namespace wcf\data\user\trophy;

use wcf\data\CollectionDatabaseObject;
use wcf\data\trophy\Trophy;
use wcf\data\trophy\TrophyCache;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\event\EventHandler;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user trophy.
 *
 * The localized custom description is stored in the `wcf1_user_trophy_l10n` table.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $userTrophyID           unique id of the user trophy
 * @property-read   int     $trophyID
 * @property-read   int     $userID
 * @property-read   int     $time                   the time when the trophy was rewarded
 * @property-read   0|1     $useCustomDescription   `1`, if the trophy use a custom description
 * @property-read   0|1     $trophyUseHtml          `1`, if the trophy use a html description
 *
 * @extends CollectionDatabaseObject<UserTrophyCollection>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
class UserTrophy extends CollectionDatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'userTrophyID';

    /**
     * The description text replacements.
     * @var string[]|null
     */
    private $replacements;

    /**
     * Returns the trophy for the user trophy.
     *
     * @return Trophy
     */
    public function getTrophy()
    {
        return TrophyCache::getInstance()->getTrophyByID($this->trophyID);
    }

    /**
     * Returns the user profile for the user trophy.
     *
     * @return UserProfile
     */
    public function getUserProfile()
    {
        return UserProfileRuntimeCache::getInstance()->getObject($this->userID);
    }

    /**
     * Returns the parsed description.
     *
     * @return string
     */
    public function getDescription()
    {
        if ($this->useCustomDescription === 0) {
            return $this->getTrophy()->getDescription();
        }

        $description = $this->getCollection()->getResolvedL10nValue($this, 'description');
        if ($this->trophyUseHtml === 0) {
            return \nl2br(
                StringUtil::encodeHTML(\strtr($description, $this->getReplacements())),
                false
            );
        }

        return \strtr($description, $this->getReplacements());
    }

    /**
     * Returns the localized values of the custom description as a
     * `languageID => value` map (see `L10nStorage`).
     *
     * @return L10nValue
     * @since 6.3
     */
    public function getL10nValues(string $columnName): array
    {
        if ($columnName !== 'description') {
            throw new \InvalidArgumentException("Invalid column name given.");
        }

        return $this->getCollection()->getL10nValues($this, $columnName);
    }

    /**
     * Returns true, if the given user can see this user trophy.
     *
     * @param User $user
     * @return  bool
     */
    public function canSee(?User $user = null)
    {
        if ($user === null) {
            $user = WCF::getUser();
        }

        if ($user->isGuest()) {
            $userProfile = UserProfile::getGuestUserProfile();
        } else {
            $userProfile = UserProfileRuntimeCache::getInstance()->getObject($user->userID);
        }

        if (!$userProfile->hasPermission('user.profile.trophy.canSeeTrophies')) {
            return false;
        }

        if ($this->getTrophy()->isDisabled()) {
            return false;
        }

        return $this->getUserProfile()->isAccessible('canViewTrophies') || $user->userID === $this->userID;
    }

    /**
     * Returns an array with replacements for the trophy.
     *
     * @return string[]
     */
    protected function getReplacements()
    {
        if ($this->replacements === null) {
            $replacements = [
                // HTML descriptions are emitted as-is, therefore the username must be
                // escaped upfront. Plaintext descriptions are escaped after the
                // replacement, escaping here would yield double encoded values.
                '{$username}' => $this->trophyUseHtml !== 0
                    ? StringUtil::encodeHTML($this->getUserProfile()->username)
                    : $this->getUserProfile()->username,
            ];

            $parameters = ['replacements' => $replacements];

            EventHandler::getInstance()->fireAction($this, 'getReplacements', $parameters);

            $this->replacements = $parameters['replacements'];
        }

        return $this->replacements;
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_user_trophy',
            'wcf1_user_trophy_l10n',
            'userTrophyID',
            ['description'],
        );
    }
}
