<?php

namespace wcf\system\gridView\user;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueueList;
use wcf\event\gridView\user\ModerationQueueGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\UserLinkColumnRenderer;
use wcf\system\interaction\bulk\user\ModerationQueueBulkInteractions;
use wcf\system\interaction\user\ModerationQueueInteractions;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\view\filter\UserFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of moderation queue entries.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<ViewableModerationQueue, ViewableModerationQueueList>
 */
final class ModerationQueueGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('title')
                ->label('wcf.global.title')
                ->titleColumn()
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof ViewableModerationQueue);
                            $title = StringUtil::encodeHTML($row->getTitle());

                            if ($row->isNew()) {
                                $badgeLabel = WCF::getLanguage()->get('wcf.message.new');
                                $badge = <<<HTML
                                        <span class="badge label newMessageBadge">{$badgeLabel}</span>
                                    HTML;
                            } else {
                                $badge = '';
                            }

                            return <<<HTML
                                    {$title}{$badge}
                                HTML;
                        }
                    }
                ),
            GridViewColumn::for("author")
                ->label("wcf.moderation.username")
                ->renderer(
                    new class extends UserLinkColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof ViewableModerationQueue);
                            $userID = $row->getAffectedObject()->getUserID();

                            if ($userID) {
                                return parent::render($userID, $row);
                            }

                            if ($row->getAffectedObject()->getUsername()) {
                                return StringUtil::encodeHTML($row->getAffectedObject()->getUsername());
                            }

                            return '';
                        }

                        #[\Override]
                        public function prepare(mixed $value, DatabaseObject $row): void
                        {
                            \assert($row instanceof ViewableModerationQueue);
                            parent::prepare($row->getAffectedObject()->getUserID(), $row);
                        }
                    }
                ),
            GridViewColumn::for("assignedUser")
                ->label("wcf.moderation.assignedUser")
                ->filter(new UserFilter('assignedUser', 'wcf.moderation.assignedUser', 'moderation_queue.assignedUserID'))
                ->sortable(sortByDatabaseColumn: "assignedUsername")
                ->renderer(
                    new class extends UserLinkColumnRenderer {
                        public function __construct()
                        {
                            parent::__construct(fallbackValue: "assignedUsername");
                        }

                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof ViewableModerationQueue);
                            return parent::render($row->assignedUserID, $row);
                        }

                        #[\Override]
                        public function prepare(mixed $value, DatabaseObject $row): void
                        {
                            \assert($row instanceof ViewableModerationQueue);
                            parent::prepare($row->assignedUserID, $row);
                        }
                    }
                ),
            GridViewColumn::for("definition")
                ->label("wcf.moderation.type")
                ->filter($this->getDefinitionFilter())
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof ViewableModerationQueue);

                            return $row->getLabel();
                        }
                    }
                ),
            GridViewColumn::for("objectType")
                ->label("wcf.moderation.objectType")
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof ViewableModerationQueue);

                            return WCF::getLanguage()->getDynamicVariable(
                                "wcf.moderation.type.{$row->getObjectTypeName()}"
                            );
                        }
                    }
                ),
            GridViewColumn::for("status")
                ->label("wcf.moderation.status")
                ->sortable(sortByDatabaseColumn: "moderation_queue.status")
                ->filter(
                    new class(
                        [
                            ModerationQueue::STATUS_OUTSTANDING => "wcf.moderation.status.outstanding",
                            ModerationQueue::STATUS_DONE => "wcf.moderation.status.done",
                        ],
                        'status',
                        'wcf.moderation.status'
                    ) extends SelectFilter {
                        #[\Override]
                        public function applyFilter(DatabaseObjectList $list, string $value): void
                        {
                            if ($value == ModerationQueue::STATUS_DONE) {
                                $list->getConditionBuilder()->add(
                                    "moderation_queue.status IN (?)",
                                    [
                                        [
                                            ModerationQueue::STATUS_DONE,
                                            ModerationQueue::STATUS_CONFIRMED,
                                            ModerationQueue::STATUS_REJECTED
                                        ]
                                    ]
                                );
                            } else {
                                $list->getConditionBuilder()->add(
                                    "moderation_queue.status IN (?)",
                                    [[ModerationQueue::STATUS_OUTSTANDING, ModerationQueue::STATUS_PROCESSING]]
                                );
                            }
                        }
                    }
                )
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof ViewableModerationQueue);

                            return StringUtil::encodeHTML($row->getStatus());
                        }
                    }
                ),
            GridViewColumn::for("comments")
                ->label("wcf.global.comments")
                ->sortable(sortByDatabaseColumn: "moderation_queue.comments")
                ->filter(new IntegerFilter('comments', 'wcf.global.comments', 'moderation_queue.comments'))
                ->renderer(new NumberColumnRenderer()),
            GridViewColumn::for("lastChangeTime")
                ->label("wcf.moderation.lastChangeTime")
                ->sortable(sortByDatabaseColumn: "moderation_queue.lastChangeTime")
                ->filter(new TimeFilter('lastChangeTime', 'wcf.moderation.lastChangeTime', "moderation_queue.lastChangeTime"))
                ->renderer(new TimeColumnRenderer()),
        ]);

        $provider = new ModerationQueueInteractions();
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new ModerationQueueBulkInteractions());

        $this->setDefaultSortField("lastChangeTime");
        $this->setDefaultSortOrder("DESC");
        $this->addRowLink(new GridViewRowLink(isLinkableObject: true));
    }

    private function getDefinitionFilter(): SelectFilter
    {
        return new class extends SelectFilter {
            public function __construct()
            {
                parent::__construct(
                    $this->getDefinitionOptions(),
                    'definition',
                    'wcf.moderation.type',
                    'moderation_queue.objectTypeID'
                );
            }

            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $value): void
            {
                $columnName = $this->getDatabaseColumnName($list);

                $list->getConditionBuilder()->add("{$columnName} IN (?)", [
                    ModerationQueueManager::getInstance()->getObjectTypeIDs([(int)$value]),
                ]);
            }

            /**
             * @return string[]
             */
            private function getDefinitionOptions(): array
            {
                return \array_map(
                    static fn($definition) => 'wcf.moderation.type.' . $definition,
                    ModerationQueueManager::getInstance()->getDefinitions()
                );
            }
        };
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('mod.general.canUseModeration');
    }

    #[\Override]
    protected function createObjectList(): ViewableModerationQueueList
    {
        return new ViewableModerationQueueList();
    }

    #[\Override]
    protected function getInitializedEvent(): ModerationQueueGridViewInitialized
    {
        return new ModerationQueueGridViewInitialized($this);
    }
}
