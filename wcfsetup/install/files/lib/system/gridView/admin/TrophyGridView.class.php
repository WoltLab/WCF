<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\TrophyEditForm;
use wcf\data\DatabaseObject;
use wcf\data\trophy\L10nTrophyList;
use wcf\data\trophy\Trophy;
use wcf\event\gridView\admin\TrophyGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\TrophyInteractions;
use wcf\system\interaction\bulk\admin\TrophyBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\L10nTextFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of trophies.
 *
 * @author Olaf Braun, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends AbstractGridView<Trophy, L10nTrophyList>
 */
final class TrophyGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for("trophyID")
                ->label("wcf.global.objectID")
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for("image")
                ->label("wcf.acp.trophy")
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Trophy);

                            return $row->renderTrophy();
                        }

                        #[\Override]
                        public function getClasses(): string
                        {
                            return "gridView__column--icon";
                        }
                    }
                ),
            GridViewColumn::for("title")
                ->titleColumn()
                ->label("wcf.global.title")
                ->filter(new L10nTextFilter(
                    Trophy::getL10nDefinition(),
                    "title",
                    "title",
                    "wcf.global.title",
                ))
                ->sortable(sortByDatabaseColumn: "title"),
            GridViewColumn::for("showOrder")
                ->label("wcf.global.showOrder")
                ->renderer(new NumberColumnRenderer())
                ->filter(IntegerFilter::class)
                ->sortable(),
        ]);

        $this->addAvailableFilters([
            new L10nTextFilter(
                Trophy::getL10nDefinition(),
                "description",
                "description",
                "wcf.global.description",
            ),
        ]);

        $provider = new TrophyInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(TrophyEditForm::class),
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new TrophyBulkInteractions());

        $this->addQuickInteraction(
            new ToggleInteraction(
                'enable',
                'core/trophies/%s/enable',
                'core/trophies/%s/disable'
            )
        );

        $this->setDefaultSortField("showOrder");
        $this->addRowLink(new GridViewRowLink(TrophyEditForm::class));
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_TROPHY !== 0
            && WCF::getSession()->hasPermission("admin.trophy.canManageTrophy");
    }

    #[\Override]
    protected function createObjectList(): L10nTrophyList
    {
        return new L10nTrophyList();
    }

    #[\Override]
    protected function getInitializedEvent(): TrophyGridViewInitialized
    {
        return new TrophyGridViewInitialized($this);
    }
}
