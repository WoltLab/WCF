<?php

namespace wcf\system\listView\user;

use wcf\data\poll\option\PollOption;
use wcf\data\poll\Poll;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\event\listView\user\PollParticipantListViewInitialized;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * List view that shows the participants of a poll.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class PollParticipantListView extends AbstractSimpleUserListView
{
    /**
     * @var array<int, list<int>>
     */
    private array $userVotes;

    /**
     * @var array<int, PollOption>
     */
    private array $pollOptions;
    private Poll $poll;

    public function __construct(
        public readonly int $pollID,
        public readonly ?int $optionID = null,
    ) {
        parent::__construct();

        $this->poll = new Poll($pollID);
        $this->pollOptions = $this->poll->getOptions();

        $this->setAdditionalHeaderContent($this->getSimpleFilterButtons());
    }

    #[\Override]
    protected function createObjectList(): UserProfileList
    {
        $list = new UserProfileList();

        if ($this->optionID !== null) {
            $list->getConditionBuilder()->add(
                "user_table.userID IN (SELECT userID FROM wcf1_poll_option_vote WHERE pollID = ? AND optionID = ?)",
                [$this->pollID, $this->optionID]
            );
        } else {
            $list->getConditionBuilder()->add(
                "user_table.userID IN (SELECT userID FROM wcf1_poll_option_vote WHERE pollID = ?)",
                [$this->pollID]
            );
        }

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        if ($this->poll->isNil()) {
            return false;
        } elseif (!$this->poll->canViewParticipants()) {
            return false;
        }

        return true;
    }

    #[\Override]
    protected function getInitializedEvent(): PollParticipantListViewInitialized
    {
        return new PollParticipantListViewInitialized($this);
    }

    #[\Override]
    public function getParameters(): array
    {
        $parameters = [
            'pollID' => $this->pollID,
        ];

        if ($this->optionID !== null) {
            $parameters['optionID'] = $this->optionID;
        }

        return $parameters;
    }

    #[\Override]
    public function getItemDescription(UserProfile $user): string
    {
        return StringUtil::encodeHTML(
            \implode(', ', \array_map(
                fn($optionID) => $this->pollOptions[$optionID]->optionValue,
                $this->getUserVote($user->userID)
            ))
        );
    }

    private function loadUserVotes(): void
    {
        if (isset($this->userVotes)) {
            return;
        }

        $sql = "SELECT userID, optionID FROM wcf1_poll_option_vote WHERE pollID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->pollID]);
        $this->userVotes = $statement->fetchMap('userID', 'optionID', false);
    }

    /**
     * @return list<int>
     */
    private function getUserVote(int $userID): array
    {
        $this->loadUserVotes();

        return $this->userVotes[$userID];
    }

    private function getSimpleFilterButtons(): string
    {
        $sql = "SELECT COUNT(*) AS count, optionID FROM wcf1_poll_option_vote WHERE pollID = ? GROUP BY optionID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->pollID]);
        $voteCounts = $statement->fetchMap('optionID', 'count');
        if (\count($voteCounts) <= 1) {
            // Skip filtering if only one type is present.
            return '';
        }

        $totalCount = 0;
        foreach ($voteCounts as $count) {
            $totalCount += $count;
        }

        return WCF::getTPL()->render(
            'wcf',
            'pollParticipantListViewFilterButtons',
            [
                'view' => $this,
                'totalCount' => $totalCount,
                'voteCounts' => $voteCounts,
                'options' => \array_filter(
                    $this->poll->getOptions(),
                    static fn($option) => isset($voteCounts[$option->optionID])
                ),
                'optionID' => $this->optionID,
            ]
        );
    }
}
