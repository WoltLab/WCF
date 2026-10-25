<?php

namespace wcf\system\poll;

/**
 * Snapshot of the access to the content a legacy poll belonged to, written as
 * the acl of the poll when it is converted into a standalone poll.
 *
 * The values are evaluated per representative group set (see
 * `LegacyPollConverter::createSnapshot()`), user-specific permissions are not
 * reflected. The `canViewPoll` values are omitted if every group can view the
 * content.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PollAclSnapshot
{
    /**
     * @param array<int, bool> $groupView `groupID => canViewPoll`
     * @param array<int, bool> $groupVote `groupID => canVotePoll`
     */
    public function __construct(
        public readonly array $groupView,
        public readonly array $groupVote,
    ) {}

    /**
     * Returns true if every group can view the content.
     */
    public function isPublic(): bool
    {
        return !\in_array(false, $this->groupView, true);
    }

    /**
     * Returns the values in the format expected by `ACLHandler::readValues()`.
     *
     * @param array<string, int> $optionIDs `optionName => optionID`
     * @return array{group: string, user: string}
     */
    public function toAclValues(array $optionIDs): array
    {
        $values = [];
        if (!$this->isPublic()) {
            foreach ($this->groupView as $groupID => $value) {
                $values[$groupID][$optionIDs['canViewPoll']] = (int)$value;
            }
        }
        foreach ($this->groupVote as $groupID => $value) {
            $values[$groupID][$optionIDs['canVotePoll']] = (int)$value;
        }

        return [
            'group' => \json_encode($values, \JSON_THROW_ON_ERROR),
            // An empty value is required, `ACLHandler` falls back to `$_POST` otherwise.
            'user' => '[]',
        ];
    }
}
