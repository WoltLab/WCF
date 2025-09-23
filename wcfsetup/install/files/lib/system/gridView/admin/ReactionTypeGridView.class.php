<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\ReactionTypeEditForm;
use wcf\data\DatabaseObject;
use wcf\data\reaction\type\I18nReactionTypeList;
use wcf\data\reaction\type\ReactionType;
use wcf\event\gridView\admin\ReactionTypeGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\PhraseColumnRenderer;
use wcf\system\interaction\admin\ReactionTypeInteractions;
use wcf\system\interaction\bulk\admin\ReactionTypeBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\view\filter\I18nTextFilter;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\ObjectIdFilter;
use wcf\system\WCF;

/**
 * GridView for the list of reaction types.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends AbstractGridView<ReactionType, I18nReactionTypeList>
 */
final class ReactionTypeGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for("reactionTypeID")
                ->label("wcf.global.objectID")
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(ObjectIdFilter::class)
                ->sortable(),
            GridViewColumn::for("image")
                ->label("wcf.acp.reactionType.image")
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof ReactionType);

                            return $row->renderIcon();
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
                ->renderer(new PhraseColumnRenderer())
                ->filter(I18nTextFilter::class)
                ->sortable(sortByDatabaseColumn: "titleI18n"),
            GridViewColumn::for("showOrder")
                ->label("wcf.global.showOrder")
                ->renderer(new NumberColumnRenderer())
                ->filter(IntegerFilter::class)
                ->sortable(),
        ]);

        $provider = new ReactionTypeInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(ReactionTypeEditForm::class),
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new ReactionTypeBulkInteractions());

        $this->addQuickInteraction(
            new ToggleInteraction(
                "enable",
                "core/reactions/types/%s/enable",
                "core/reactions/types/%s/disable",
                "isAssignable",
                false
            )
        );

        $this->setDefaultSortField("showOrder");
        $this->addRowLink(new GridViewRowLink(ReactionTypeEditForm::class));
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_LIKE
            && WCF::getSession()->getPermission("admin.content.reaction.canManageReactionType");
    }

    #[\Override]
    protected function createObjectList(): I18nReactionTypeList
    {
        return new I18nReactionTypeList();
    }

    protected function getInitializedEvent(): ReactionTypeGridViewInitialized
    {
        return new ReactionTypeGridViewInitialized($this);
    }
}
