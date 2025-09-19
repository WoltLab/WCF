<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\BoxEditForm;
use wcf\data\box\Box;
use wcf\data\box\BoxList;
use wcf\data\DatabaseObjectList;
use wcf\event\gridView\admin\BoxGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\BooleanFilter;
use wcf\system\gridView\filter\NumericFilter;
use wcf\system\gridView\filter\SelectFilter;
use wcf\system\gridView\filter\TextFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\BoxInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\WCF;

/**
 * Grid view for the list of boxes.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<Box, BoxList>
 */
final class BoxGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('boxID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('name')
                ->label('wcf.global.name')
                ->titleColumn()
                ->filter(new TextFilter())
                ->sortable(),
            GridViewColumn::for('title')
                ->label('wcf.global.title')
                ->filter($this->getBoxContentFilter('title'))
                ->hidden(),
            GridViewColumn::for('content')
                ->label('wcf.acp.box.content')
                ->filter($this->getBoxContentFilter('content'))
                ->hidden(),
            GridViewColumn::for('boxType')
                ->label('wcf.acp.box.type')
                ->filter(
                    new SelectFilter(
                        \array_combine(
                            Box::$availableBoxTypes,
                            \array_map(
                                static fn(string $type) => 'wcf.acp.box.type.' . $type,
                                Box::$availableBoxTypes
                            )
                        )
                    )
                )
                ->sortable(),
            GridViewColumn::for('position')
                ->label('wcf.acp.box.position')
                ->filter(
                    new SelectFilter(
                        \array_combine(
                            Box::$availablePositions,
                            \array_map(
                                static fn(string $position) => 'wcf.acp.box.position.' . $position,
                                Box::$availablePositions
                            )
                        )
                    )
                )
                ->sortable(),
            GridViewColumn::for('showOrder')
                ->label('wcf.global.showOrder')
                ->filter(new NumericFilter())
                ->sortable(),
            GridViewColumn::for('originIsSystem')
                ->label('wcf.acp.box.originIsNotSystem')
                ->filter(
                    new class extends BooleanFilter {
                        #[\Override]
                        public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
                        {
                            $columnName = $this->getDatabaseColumnName($list, $id);

                            $list->getConditionBuilder()->add("{$columnName} = ?", [0]);
                        }
                    }
                )
                ->hidden(),
        ]);

        $provider = new BoxInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(BoxEditForm::class)
        ]);
        $this->setInteractionProvider($provider);

        $this->addQuickInteraction(new ToggleInteraction('enable', 'core/boxes/%s/enable', 'core/boxes/%s/disable'));

        $this->setDefaultSortField('name');
        $this->addRowLink(new GridViewRowLink(BoxEditForm::class));
    }

    private function getBoxContentFilter(string $databaseColumn): TextFilter
    {
        return new class($databaseColumn) extends TextFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
            {
                $list->getConditionBuilder()->add(
                    "box.boxID IN (
                                    SELECT  boxID
                                    FROM    wcf1_box_content
                                    WHERE   {$this->databaseColumn} LIKE ?
                              )",
                    ['%' . WCF::getDB()->escapeLikeValue($value) . '%']
                );
            }
        };
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.content.cms.canManageBox');
    }

    #[\Override]
    protected function createObjectList(): BoxList
    {
        $boxList = new BoxList();
        $boxList->getConditionBuilder()->add('box.boxType <> ?', ['menu']);

        return $boxList;
    }

    #[\Override]
    protected function getInitializedEvent(): BoxGridViewInitialized
    {
        return new BoxGridViewInitialized($this);
    }
}
