<?php

namespace wcf\system\gridView\admin;

use LogicException;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\DatabaseObjectList;
use wcf\data\modification\log\IViewableModificationLog;
use wcf\data\modification\log\ModificationLog;
use wcf\data\modification\log\ModificationLogList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\Package;
use wcf\event\gridView\admin\ModificationLogGridViewInitialized;
use wcf\event\IPsr14Event;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\IGridViewFilter;
use wcf\system\gridView\filter\TextFilter;
use wcf\system\gridView\filter\TimeFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ILinkColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\UserLinkColumnRenderer;
use wcf\system\log\modification\IExtendedModificationLogHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of all modification log items.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ModificationLogGridView extends AbstractGridView
{
    /**
     * @var IViewableModificationLog[]
     */
    private array $logItems;

    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('logID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('userID')
                ->label('wcf.user.username')
                ->sortable(true, 'modification_log.username')
                ->renderer(new UserLinkColumnRenderer())
                ->filter(new TextFilter('modification_log.username')),
            GridViewColumn::for('action')
                ->label('wcf.acp.modificationLog.action')
                ->titleColumn()
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof DatabaseObjectDecorator);
                            $log = $row->getDecoratedObject();
                            \assert($log instanceof ModificationLog);
                            $objectType = ObjectTypeCache::getInstance()->getObjectType($log->objectTypeID);
                            if (!$objectType) {
                                return '';
                            }

                            return WCF::getLanguage()->get(
                                'wcf.acp.modificationLog.' . $objectType->objectType . '.' . $log->action
                            );
                        }
                    },
                ])
                ->filter(
                    new class($this->getAvailableActions()) implements IGridViewFilter {
                        public function __construct(private readonly array $options) {}

                        #[\Override]
                        public function getFormField(string $id, string $label): AbstractFormField
                        {
                            return SelectFormField::create($id)
                                ->label($label)
                                ->options($this->options, false, false);
                        }

                        #[\Override]
                        public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
                        {
                            if (\is_numeric($value)) {
                                $list->getConditionBuilder()->add(
                                    "objectTypeID IN (SELECT objectTypeID FROM wcf1_object_type WHERE packageID = ?)",
                                    [$value]
                                );
                            } else if (\preg_match('~^(?P<objectType>.+)\-(?P<action>[^\-]+)$~', $value, $matches)) {
                                $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
                                    'com.woltlab.wcf.modifiableContent',
                                    $matches['objectType']
                                );
                                if (!$objectType) {
                                    return;
                                }

                                $list->getConditionBuilder()->add(
                                    "objectTypeID = ? AND action = ?",
                                    [$objectType->objectTypeID, $matches['action']]
                                );
                            }
                        }

                        #[\Override]
                        public function renderValue(string $value): string
                        {
                            if (\is_numeric($value)) {
                                return WCF::getLanguage()->get($this->options[$value]);
                            }

                            return \substr(WCF::getLanguage()->get($this->options[$value]), 24);
                        }
                    }
                ),
            GridViewColumn::for('affectedObject')
                ->label('wcf.acp.modificationLog.affectedObject')
                ->renderer([
                    new class extends DefaultColumnRenderer implements ILinkColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof IViewableModificationLog);
                            if ($row->getAffectedObject() === null) {
                                return WCF::getLanguage()->get('wcf.acp.modificationLog.affectedObject.unknown');
                            }

                            return \sprintf(
                                '<a href="%s">%s</a>',
                                StringUtil::encodeHTML($row->getAffectedObject()->getLink()),
                                StringUtil::encodeHTML($row->getAffectedObject()->getTitle())
                            );
                        }
                    },
                ]),
            GridViewColumn::for('time')
                ->label('wcf.global.date')
                ->sortable()
                ->renderer(new TimeColumnRenderer())
                ->filter(new TimeFilter()),
        ]);

        $this->setSortField('time');
        $this->setSortOrder('DESC');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.management.canViewLog');
    }

    #[\Override]
    protected function createObjectList(): DatabaseObjectList
    {
        return new ModificationLogList();
    }

    #[\Override]
    public function getRows(): array
    {
        if (!isset($this->logItems)) {
            $this->logItems = [];
            $this->getObjectList()->readObjects();

            $itemsPerType = [];
            foreach ($this->getObjectList() as $modificationLog) {
                if (!isset($itemsPerType[$modificationLog->objectTypeID])) {
                    $itemsPerType[$modificationLog->objectTypeID] = [];
                }

                $itemsPerType[$modificationLog->objectTypeID][] = $modificationLog;
            }

            if (!empty($itemsPerType)) {
                foreach ($itemsPerType as $objectTypeID => $items) {
                    $objectType = ObjectTypeCache::getInstance()->getObjectType($objectTypeID);
                    if (!$objectType) {
                        continue;
                    }
                    $processor = $objectType->getProcessor();
                    if (!$processor) {
                        continue;
                    }
                    \assert($processor instanceof IExtendedModificationLogHandler);

                    $this->logItems = \array_merge(
                        $this->logItems,
                        $processor->processItems($items)
                    );
                }
            }

            DatabaseObject::sort($this->logItems, $this->getSortField(), $this->getSortOrder());
        }

        return $this->logItems;
    }

    #[\Override]
    protected function getInitializedEvent(): ?IPsr14Event
    {
        return new ModificationLogGridViewInitialized($this);
    }

    private function getAvailableActions(): array
    {
        $packages = $actions = $availableActions = [];

        foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.modifiableContent') as $objectType) {
            $processor = $objectType->getProcessor();
            if ($processor === null) {
                continue;
            }
            \assert($processor instanceof IExtendedModificationLogHandler);

            if (!$processor->includeInLogList()) {
                continue;
            }

            if (!isset($packages[$objectType->packageID])) {
                $actions[$objectType->packageID] = [];
                $packages[$objectType->packageID] = $objectType->getPackage();
            }

            foreach ($processor->getAvailableActions() as $action) {
                $actions[$objectType->packageID]["{$objectType->objectType}-{$action}"]
                    = WCF::getLanguage()->get("wcf.acp.modificationLog.{$objectType->objectType}.{$action}");
            }
        }

        foreach ($actions as &$actionsPerPackage) {
            \asort($actionsPerPackage, \SORT_NATURAL);
        }
        \uasort($packages, static function (Package $a, Package $b) {
            return \strnatcasecmp($a->package, $b->package);
        });

        foreach ($packages as $package) {
            $availableActions[$package->packageID]
                = WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.modificationLog.action.allPackageActions',
                    ['package' => $package]
                );

            foreach ($actions[$package->packageID] as $actionName => $actionLabel) {
                $availableActions[$actionName] = \str_repeat('&nbsp;', 4) . $actionLabel;
            }
        }

        return $availableActions;
    }
}
