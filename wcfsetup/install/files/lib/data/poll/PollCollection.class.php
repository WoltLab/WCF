<?php

namespace wcf\data\poll;

use wcf\data\DatabaseObjectCollection;
use wcf\data\poll\option\PollOption;
use wcf\data\poll\option\PollOptionList;
use wcf\data\TCollectionL10n;
use wcf\data\TCollectionUserProfiles;
use wcf\system\acl\ACLHandler;
use wcf\system\l10n\L10nDefinition;
use wcf\system\WCF;

/**
 * Represents a collection of polls.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectCollection<Poll>
 * @phpstan-type AclPermissions array{group: array<int, array<string, int>>, user: array<int, array<string, int>>}
 */
class PollCollection extends DatabaseObjectCollection
{
    use TCollectionL10n;
    use TCollectionUserProfiles;

    /**
     * @var array<int, array<int, PollOption>>
     */
    private array $options;

    /**
     * @var array<int, AclPermissions>
     */
    private array $aclPermissions;

    /**
     * Returns the options of the given poll including the vote state of the
     * active user (`voted`).
     *
     * @return array<int, PollOption>
     */
    public function getOptions(Poll $poll): array
    {
        $this->loadOptions();

        return $this->options[$poll->pollID] ?? [];
    }

    private function loadOptions(): void
    {
        if (isset($this->options)) {
            return;
        }

        $this->options = [];

        $optionList = new PollOptionList();
        $optionList->getConditionBuilder()->add("poll_option.pollID IN (?)", [$this->getObjectIDs()]);
        if (WCF::getUser()->isGuest()) {
            $optionList->sqlSelects = "'0' AS voted";
        } else {
            $optionList->sqlSelects = "CASE WHEN poll_option_vote.optionID IS NULL THEN '0' ELSE '1' END AS voted";
            $optionList->sqlJoins = "
                LEFT JOIN   wcf1_poll_option_vote poll_option_vote
                ON          poll_option_vote.optionID = poll_option.optionID
                        AND poll_option_vote.userID = " . WCF::getUser()->userID;
        }
        $optionList->sqlOrderBy = "poll_option.showOrder ASC";
        $optionList->readObjects();

        foreach ($optionList as $option) {
            $this->options[$option->pollID][$option->optionID] = $option;
        }
    }

    /**
     * Returns the acl values of the given standalone poll by option name for
     * groups and users.
     *
     * @return AclPermissions
     */
    public function getAclPermissions(Poll $poll): array
    {
        $this->loadAclPermissions();

        return $this->aclPermissions[$poll->pollID] ?? ['group' => [], 'user' => []];
    }

    private function loadAclPermissions(): void
    {
        if (isset($this->aclPermissions)) {
            return;
        }

        $this->aclPermissions = [];

        $pollIDs = \array_keys(\array_filter(
            $this->getObjects(),
            static fn(Poll $poll) => $poll->isStandalone()
        ));
        if ($pollIDs === []) {
            return;
        }

        $data = ACLHandler::getInstance()->getPermissions(
            ACLHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.poll'),
            $pollIDs
        );
        $options = $data['options']->getObjects();

        foreach (['group', 'user'] as $type) {
            foreach ($data[$type] as $pollID => $values) {
                foreach ($values as $typeID => $optionValues) {
                    foreach ($optionValues as $optionID => $optionValue) {
                        $this->aclPermissions[$pollID][$type][$typeID][$options[$optionID]->optionName] = (int)$optionValue;
                    }
                }
            }
        }
    }

    #[\Override]
    protected function getL10nDefinition(): L10nDefinition
    {
        return Poll::getL10nDefinition();
    }
}
