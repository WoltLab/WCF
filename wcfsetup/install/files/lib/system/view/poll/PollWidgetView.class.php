<?php

namespace wcf\system\view\poll;

use wcf\data\poll\Poll;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Renders a standalone poll that has been embedded into a message.
 *
 * The output contains both the vote form and the results, the client switches
 * between them without reloading the poll.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PollWidgetView implements \Stringable
{
    public function __construct(
        private readonly Poll $poll,
    ) {}

    #[\Override]
    public function __toString(): string
    {
        // The order of the results can differ from the order of the options,
        // the results are reordered through CSS to keep the rows in place.
        $resultPositions = [];
        $leadingVotes = 0;
        foreach (\array_values($this->poll->getOptions(true)) as $position => $option) {
            $resultPositions[$option->optionID] = $position;
            $leadingVotes = \max($leadingVotes, $option->votes);
        }

        return WCF::getTPL()->render('wcf', 'shared_bbcode_poll', [
            'poll' => $this->poll,
            'options' => $this->poll->getOptions(),
            'resultPositions' => $resultPositions,
            'leadingVotes' => $leadingVotes,
            'initialState' => $this->getInitialState(),
            // The same poll can be embedded multiple times on a page.
            'elementID' => 'poll' . $this->poll->pollID . '_' . \substr(StringUtil::getRandomID(), 0, 8),
        ]);
    }

    /**
     * @return 'vote'|'results'
     */
    private function getInitialState(): string
    {
        if ($this->poll->canVote() && !$this->poll->isParticipant()) {
            return 'vote';
        }

        if ($this->poll->canSeeResult()) {
            return 'results';
        }

        return 'vote';
    }
}
