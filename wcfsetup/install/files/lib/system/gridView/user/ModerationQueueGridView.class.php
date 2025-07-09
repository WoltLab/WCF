<?php

namespace wcf\system\gridView\user;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueueList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\event\gridView\user\ModerationQueueGridViewInitialized;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\NumericFilter;
use wcf\system\gridView\filter\SelectFilter;
use wcf\system\gridView\filter\TimeFilter;
use wcf\system\gridView\filter\UserFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\UserLinkColumnRenderer;
use wcf\system\interaction\bulk\user\ModerationQueueBulkInteractions;
use wcf\system\interaction\user\ModerationQueueInteractions;
use wcf\system\moderation\queue\ModerationQueueManager;
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
    public function __construct(?int $status = null)
    {
        $this->addColumns([
            GridViewColumn::for('title')
                ->label('wcf.global.title')
                ->titleColumn()
                ->renderer(
                    new
                        class extends DefaultColumnRenderer {
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
                    new
                        class extends UserLinkColumnRenderer {
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
                ->filter(new UserFilter("moderation_queue.assignedUserID"))
                ->sortable(sortByDatabaseColumn: "assignedUsername")
                ->renderer(
                    new
                        class extends UserLinkColumnRenderer {
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
            GridViewColumn::for("objectType")
                ->label("wcf.moderation.report.reportedContent")
                ->filter($this->getObjectTypeFilter())
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof ViewableModerationQueue);

                            return WCF::getLanguage()->getDynamicVariable(
                                "wcf.moderation.type.{$row->getObjectTypeName()}"
                            );
                        }
                    }
                )
                ->sortable(sortByDatabaseColumn: "moderation_queue.objectTypeID"),
            GridViewColumn::for("status")
                ->label("wcf.moderation.status")
                ->sortable(sortByDatabaseColumn: "moderation_queue.status")
                ->filter(
                    new class([
                        ModerationQueue::STATUS_OUTSTANDING => "wcf.moderation.status.outstanding",
                        ModerationQueue::STATUS_DONE => "wcf.moderation.status.done",
                    ]) extends SelectFilter {
                        #[\Override]
                        public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
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
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof ViewableModerationQueue);

                            return StringUtil::encodeHTML($row->getStatus());
                        }
                    }
                ),
            GridViewColumn::for("comments")
                ->label("wcf.moderation.comments")
                ->sortable(sortByDatabaseColumn: "moderation_queue.comments")
                ->filter(new NumericFilter("moderation_queue.comments"))
                ->renderer(new NumberColumnRenderer()),
            GridViewColumn::for("lastChangeTime")
                ->label("wcf.moderation.lastChangeTime")
                ->sortable(sortByDatabaseColumn: "moderation_queue.lastChangeTime")
                ->filter(new TimeFilter("moderation_queue.lastChangeTime"))
                ->renderer(new TimeColumnRenderer()),
        ]);

        $provider = new ModerationQueueInteractions();
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new ModerationQueueBulkInteractions());

        $this->setSortField("lastChangeTime");
        $this->setSortOrder("DESC");
        $this->addRowLink(new GridViewRowLink(isLinkableObject: true));

        if ($status !== null) {
            $this->setActiveFilters([
                "status" => $status
            ]);
        }
    }

    private function getObjectTypeFilter(): SelectFilter
    {
        return new class extends SelectFilter {
            public function __construct()
            {
                parent::__construct($this->getModerationQueueObjectTypeIDs(), "moderation_queue.objectTypeID");
            }

            #[\Override]
            public function getFormField(string $id, string $label): AbstractFormField
            {
                return SelectFormField::create($id)
                    ->label($label)
                    ->options($this->getModerationQueueObjectTypeOptions(), true);
            }

            /**
             * @return array<int, string>
             */
            private function getModerationQueueObjectTypeIDs(): array
            {
                $objectTypes = [];
                foreach (ModerationQueueManager::getInstance()->getDefinitionNamesByObjectTypeIDs() as $objectTypeID => $definition) {
                    $objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
                    $objectTypes[$objectTypeID] = \sprintf(
                        "%s - %s",
                        WCF::getLanguage()->getDynamicVariable('wcf.moderation.type.' . $definition),
                        WCF::getLanguage()->getDynamicVariable('wcf.moderation.type.' . $objectType->objectType),
                    );
                }

                return $objectTypes;
            }

            /**
             * @return list<array{
             *  value: string,
             *  depth: int,
             *  isSelectable: bool,
             *  label: string,
             * }>
             */
            private function getModerationQueueObjectTypeOptions(): array
            {
                $options = [];
                foreach (ModerationQueueManager::getInstance()->getDefinitions() as $definitionName) {
                    $options[] = [
                        "value" => $definitionName,
                        "depth" => 0,
                        "isSelectable" => false,
                        "label" => WCF::getLanguage()->getDynamicVariable('wcf.moderation.type.' . $definitionName)
                    ];

                    foreach (ObjectTypeCache::getInstance()->getObjectTypes($definitionName) as $objectType) {
                        $options[] = [
                            "value" => $objectType->objectTypeID,
                            "depth" => 1,
                            "isSelectable" => true,
                            "label" => WCF::getLanguage()->getDynamicVariable(
                                'wcf.moderation.type.' . $objectType->objectType
                            ),
                        ];
                    }
                }

                return $options;
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
