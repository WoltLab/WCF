<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\BoxEditForm;
use wcf\data\box\Box;
use wcf\data\box\BoxList;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\event\gridView\admin\BoxGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\PhraseColumnRenderer;
use wcf\system\interaction\admin\BoxInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\view\filter\BooleanFilter;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TextFilter;
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
                ->filter(TextFilter::class)
                ->sortable(),
            GridViewColumn::for('boxType')
                ->label('wcf.acp.box.type')
                ->renderer(new class extends PhraseColumnRenderer {
                    #[\Override]
                    public function render(mixed $value, DatabaseObject $row): string
                    {
                        return parent::render('wcf.acp.box.type.' . $value, $row);
                    }
                })
                ->filter(
                    new SelectFilter(
                        \array_combine(
                            Box::$availableBoxTypes,
                            \array_map(
                                static fn(string $type) => 'wcf.acp.box.type.' . $type,
                                Box::$availableBoxTypes
                            )
                        ),
                        'boxType',
                        'wcf.acp.box.type'
                    )
                )
                ->sortable(),
            GridViewColumn::for('position')
                ->label('wcf.acp.box.position')
                ->renderer(new class extends PhraseColumnRenderer {
                    #[\Override]
                    public function render(mixed $value, DatabaseObject $row): string
                    {
                        return parent::render('wcf.acp.box.position.' . $value, $row);
                    }
                })
                ->filter(
                    new SelectFilter(
                        \array_combine(
                            Box::$availablePositions,
                            \array_map(
                                static fn(string $position) => 'wcf.acp.box.position.' . $position,
                                Box::$availablePositions
                            )
                        ),
                        'position',
                        'wcf.acp.box.position'
                    )
                )
                ->sortable(),
            GridViewColumn::for('showOrder')
                ->label('wcf.global.showOrder')
                ->renderer(new NumberColumnRenderer())
                ->filter(IntegerFilter::class)
                ->sortable(),
        ]);

        $this->addAvailableFilters([
            new class('title', 'wcf.global.title') extends TextFilter {
                #[\Override]
                public function applyFilter(DatabaseObjectList $list, string $value): void
                {
                    $list->getConditionBuilder()->add(
                        "box.boxID IN (
                            SELECT  boxID
                            FROM    wcf1_box_content
                            WHERE   title LIKE ?
                        )",
                        ['%' . WCF::getDB()->escapeLikeValue($value) . '%']
                    );
                }
            },
            new class('content', 'wcf.acp.box.content') extends TextFilter {
                #[\Override]
                public function applyFilter(DatabaseObjectList $list, string $value): void
                {
                    $list->getConditionBuilder()->add(
                        "box.boxID IN (
                            SELECT  boxID
                            FROM    wcf1_box_content
                            WHERE   content LIKE ?
                        )",
                        ['%' . WCF::getDB()->escapeLikeValue($value) . '%']
                    );
                }
            },
            new BooleanFilter('originIsNotSystem', 'wcf.acp.box.originIsNotSystem', 'originIsSystem', true),
        ]);
        $provider = new BoxInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(BoxEditForm::class)
        ]);
        $this->setInteractionProvider($provider);

        $this->addQuickInteraction(new ToggleInteraction(
            'enable',
            'core/boxes/%s/enable',
            'core/boxes/%s/disable'
        ));

        $this->setDefaultSortField('name');
        $this->addRowLink(new GridViewRowLink(BoxEditForm::class));
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
