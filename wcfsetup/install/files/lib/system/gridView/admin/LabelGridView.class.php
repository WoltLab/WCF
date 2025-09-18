<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\LabelEditForm;
use wcf\data\DatabaseObject;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\data\label\I18nLabelList;
use wcf\data\label\Label;
use wcf\event\gridView\admin\LabelGridViewInitialized;
use wcf\system\cache\builder\LabelCacheBuilder;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\I18nTextFilter;
use wcf\system\gridView\filter\NumericFilter;
use wcf\system\gridView\filter\ObjectIdFilter;
use wcf\system\gridView\filter\SelectFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\LabelInteractions;
use wcf\system\interaction\bulk\admin\LabelBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of labels.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends AbstractGridView<Label, I18nLabelList>
 */
final class LabelGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for("labelID")
                ->label("wcf.global.objectID")
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(new ObjectIdFilter())
                ->sortable(),
            GridViewColumn::for("label")
                ->label("wcf.acp.label.label")
                ->titleColumn()
                ->filter(new I18nTextFilter())
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Label);
                            $badgeClassName = StringUtil::encodeHTML($row->getClassNames());
                            $label = StringUtil::encodeHTML($row->getTitle());

                            return <<<HTML
                                <span class="badge label {$badgeClassName}">{$label}</span>
                            HTML;
                        }
                    }
                )
                ->sortable(sortByDatabaseColumn: "labelI18n"),
            GridViewColumn::for("groupID")
                ->label("wcf.acp.label.group")
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Label);

                            $groups = LabelCacheBuilder::getInstance()->getData(arrayIndex: "groups");
                            $group = $groups[$row->groupID];
                            \assert($group instanceof ViewableLabelGroup);

                            if (empty($group->groupDescription)) {
                                return StringUtil::encodeHTML($group->getTitle());
                            }
                            return \sprintf(
                                "%s / %s",
                                StringUtil::encodeHTML($group->getTitle()),
                                StringUtil::encodeHTML($group->groupDescription)
                            );
                        }
                    }
                )
                ->filter(
                    new SelectFilter(
                        \array_map(
                            static fn(ViewableLabelGroup $group) => $group->getTitle(),
                            LabelCacheBuilder::getInstance()->getData(arrayIndex: "groups"),
                        ),
                    )
                )
                ->sortable(),
            GridViewColumn::for("showOrder")
                ->label("wcf.global.showOrder")
                ->renderer(new NumberColumnRenderer())
                ->filter(new NumericFilter())
                ->sortable(),
        ]);

        $provider = new LabelInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(LabelEditForm::class),
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new LabelBulkInteractions());

        $this->setDefaultSortField("label");
        $this->addRowLink(new GridViewRowLink(LabelEditForm::class));
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission("admin.content.label.canManageLabel");
    }

    #[\Override]
    protected function createObjectList(): I18nLabelList
    {
        return new I18nLabelList();
    }

    #[\Override]
    protected function getInitializedEvent(): LabelGridViewInitialized
    {
        return new LabelGridViewInitialized($this);
    }
}
