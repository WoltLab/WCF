<?php

namespace wcf\system\poll;

use wcf\data\poll\Poll;
use wcf\data\user\group\UserGroup;
use wcf\system\acl\ACLHandler;
use wcf\system\WCF;

/**
 * Converts polls that belong to a poll container into standalone polls that
 * are embedded into the content via the `[poll]` bbcode. Used by the rebuild
 * data workers of the poll containers.
 *
 * Standalone polls are accessible independently of the content, the access to
 * the content is therefore stored as the acl of the poll, see `PollAclSnapshot`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class LegacyPollConverter
{
    /**
     * Converts the given poll into a standalone poll owned by the given user
     * and stores the given acl values.
     */
    public static function convert(Poll $poll, ?int $userID, string $username, PollAclSnapshot $snapshot): void
    {
        if ($poll->isStandalone()) {
            throw new \InvalidArgumentException("The poll '{$poll->pollID}' is already a standalone poll.");
        }

        $objectTypeID = ACLHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.poll');
        $optionIDs = [];
        foreach (ACLHandler::getInstance()->getOptions($objectTypeID) as $option) {
            $optionIDs[$option->optionName] = $option->optionID;
        }

        WCF::getDB()->beginTransaction();
        $committed = false;
        try {
            $sql = "UPDATE  wcf1_poll
                    SET     objectTypeID = NULL,
                            objectID = 0,
                            userID = ?,
                            username = ?
                    WHERE   pollID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$userID, $username, $poll->pollID]);

            ACLHandler::getInstance()->readValues($objectTypeID, $snapshot->toAclValues($optionIDs));
            ACLHandler::getInstance()->save($poll->pollID, $objectTypeID);

            WCF::getDB()->commitTransaction();
            $committed = true;
        } finally {
            ACLHandler::getInstance()->resetValues($objectTypeID);

            if (!$committed) {
                WCF::getDB()->rollBackTransaction();
            }
        }
    }

    /**
     * Prepends the `[poll]` bbcode of the given poll to the given message, the
     * legacy polls were displayed above the message.
     */
    public static function prependEmbed(string $message, int $pollID, bool $isHtml): string
    {
        if (!$isHtml) {
            return "[poll={$pollID}][/poll]\n" . $message;
        }

        return \sprintf(
            '<woltlab-metacode data-name="poll" data-attributes="%s"></woltlab-metacode>',
            \base64_encode(\json_encode([$pollID], \JSON_THROW_ON_ERROR))
        ) . $message;
    }

    /**
     * Evaluates the access to the content for each group and returns the
     * resulting snapshot.
     *
     * The callbacks receive the representative group set of the group, see
     * `getRepresentativeGroupSets()`, and whether the set represents the
     * guests. Guests can never vote. The everyone group is always denied, which
     * ensures that the acl always yields a value for every user.
     *
     * @param callable(list<int>, bool): bool $canView
     * @param callable(list<int>, bool): bool $canVote
     */
    public static function createSnapshot(callable $canView, callable $canVote): PollAclSnapshot
    {
        $guestGroupIDs = UserGroup::getGroupIDsByType([UserGroup::GUESTS]);

        $groupView = $groupVote = [];
        foreach (self::getRepresentativeGroupSets() as $groupID => $groupIDs) {
            if ($groupIDs === []) {
                $groupView[$groupID] = $groupVote[$groupID] = false;

                continue;
            }

            $isGuest = \array_intersect($guestGroupIDs, $groupIDs) !== [];
            $groupView[$groupID] = $canView($groupIDs, $isGuest);
            $groupVote[$groupID] = !$isGuest && $canVote($groupIDs, $isGuest);
        }

        return new PollAclSnapshot($groupView, $groupVote);
    }

    /**
     * Returns the group set that represents the members of each group:
     *
     * - everyone: `[]`, the group is always denied
     * - guests: `[EVERYONE, GUESTS]`, which equals the groups of a guest
     * - registered users: `[EVERYONE, USERS]`
     * - any other group: `[EVERYONE, USERS, groupID]`, because the members of
     *   a group are always registered users
     *
     * @return array<int, list<int>> `groupID => groupIDs`
     */
    private static function getRepresentativeGroupSets(): array
    {
        $everyone = UserGroup::getGroupIDsByType([UserGroup::EVERYONE]);
        $guests = UserGroup::getGroupIDsByType([UserGroup::GUESTS]);
        $users = UserGroup::getGroupIDsByType([UserGroup::USERS]);

        $groupSets = [];
        foreach (UserGroup::getAllGroups() as $group) {
            $groupSets[$group->groupID] = match ($group->groupType) {
                UserGroup::EVERYONE => [],
                UserGroup::GUESTS => [...$everyone, ...$guests],
                UserGroup::USERS => [...$everyone, ...$users],
                default => [...$everyone, ...$users, $group->groupID],
            };
        }

        return $groupSets;
    }
}
