<?php

namespace wcf\system\listView\user;

use wcf\action\PollEditAction;
use wcf\data\poll\Poll;
use wcf\data\poll\PollList;
use wcf\event\listView\user\PollManagerListViewInitialized;
use wcf\system\interaction\InteractionContextMenuComponentConfiguration;
use wcf\system\interaction\user\PollInteractions;
use wcf\system\l10n\L10nStorage;
use wcf\system\listView\AbstractListView;
use wcf\system\listView\ListViewPrimaryButton;
use wcf\system\listView\ListViewSortField;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * List view of the poll manager that is used to embed standalone polls into
 * messages.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractListView<Poll, PollList>
 */
class PollManagerListView extends AbstractListView
{
    public function __construct(
        public readonly string $searchString = '',
    ) {
        $this->addAvailableSortFields([
            new ListViewSortField('time', 'wcf.poll.time', defaultSortOrder: 'DESC'),
            new ListViewSortField('question', 'wcf.poll.question', 'question'),
            new ListViewSortField('votes', 'wcf.poll.votes', defaultSortOrder: 'DESC'),
        ]);

        // The selection is handled by the poll manager itself.
        $this->setInteractionProvider(new PollInteractions());
        $this->setInteractionContextMenuComponentConfiguration(new InteractionContextMenuComponentConfiguration(
            buttonCssClassName: 'pollManager__item__menuButton'
        ));

        $this->setAdditionalHeaderContent(\sprintf(
            '<label class="pollManager__search">%s<input type="search" class="pollManager__searchInput" placeholder="%s" value="%s"></label>'
                . '<div class="pollManager__config" data-add-endpoint="%s" hidden></div>',
            FontAwesomeIcon::fromValues('magnifying-glass')->toHtml(16),
            StringUtil::encodeHTML(WCF::getLanguage()->get('wcf.poll.search.placeholder')),
            StringUtil::encodeHTML($this->searchString),
            StringUtil::encodeHTML(LinkHandler::getInstance()->getControllerLink(PollEditAction::class))
        ));

        if (WCF::getSession()->hasPermission('user.poll.canCreatePoll')) {
            $this->setPrimaryButton(ListViewPrimaryButton::forButton(
                'wcf.poll.add',
                FontAwesomeIcon::fromValues('plus'),
                'pollManager__addButton'
            ));
        }

        $this->setItemsPerPage(30);
        $this->setDefaultSortField('time');
        $this->setDefaultSortOrder('DESC');
        $this->setCssClassName('pollManager');
    }

    #[\Override]
    protected function createObjectList(): PollList
    {
        $list = new PollList();
        $list->getConditionBuilder()->add('poll.objectTypeID IS NULL');

        // required to sort and search by the localized question
        $questionSelect = (new L10nStorage(Poll::getL10nDefinition()))->getSubSelect('question', 'poll');
        if ($list->sqlSelects !== '') {
            $list->sqlSelects .= ',';
        }
        $list->sqlSelects .= $questionSelect . ' AS question';

        if (!WCF::getSession()->hasPermission('mod.poll.canManagePoll')) {
            $list->getConditionBuilder()->add('poll.userID = ?', [WCF::getUser()->userID]);
        }

        if ($this->searchString !== '') {
            $list->getConditionBuilder()->add(
                $questionSelect . ' LIKE ?',
                ['%' . WCF::getDB()->escapeLikeValue($this->searchString) . '%']
            );
        }

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        if (\MODULE_POLL === 0 || WCF::getUser()->userID === 0) {
            return false;
        }

        return WCF::getSession()->hasPermission('user.poll.canCreatePoll')
            || WCF::getSession()->hasPermission('mod.poll.canManagePoll');
    }

    #[\Override]
    public function getParameters(): array
    {
        return [
            'searchString' => $this->searchString,
        ];
    }

    #[\Override]
    public function renderItems(): string
    {
        return WCF::getTPL()->render('wcf', 'shared_pollManagerListItems', ['view' => $this]);
    }

    #[\Override]
    protected function getInitializedEvent(): PollManagerListViewInitialized
    {
        return new PollManagerListViewInitialized($this);
    }
}
