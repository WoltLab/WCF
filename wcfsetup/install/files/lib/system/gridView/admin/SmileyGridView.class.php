<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\SmileyEditForm;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\smiley\I18nSmileyList;
use wcf\data\smiley\Smiley;
use wcf\data\smiley\SmileyCache;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\I18nTextFilter;
use wcf\system\gridView\filter\NumericFilter;
use wcf\system\gridView\filter\SelectFilter;
use wcf\system\gridView\filter\TextFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\CategoryColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\PhraseColumnRenderer;
use wcf\system\interaction\admin\SmileyInteractions;
use wcf\system\interaction\bulk\admin\SmileyBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends AbstractGridView<Smiley, I18nSmileyList>
 */
final class SmileyGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('smileyID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for("image")
                ->label("wcf.acp.smiley.smiley")
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Smiley);

                            return $row->getHtml();
                        }

                        #[\Override]
                        public function getClasses(): string
                        {
                            return "gridView__column--icon";
                        }
                    }
                ),
            GridViewColumn::for('smileyTitle')
                ->label('wcf.global.title')
                ->titleColumn()
                ->filter(new I18nTextFilter())
                ->renderer(new PhraseColumnRenderer())
                ->sortable(sortByDatabaseColumn: "smileyTitleI18n"),
            GridViewColumn::for('aliases')
                ->label('wcf.acp.smiley.aliases')
                ->filter(new TextFilter())
                ->sortable()
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Smiley);

                            $aliases = '';
                            foreach ($row->getAliases() as $alias) {
                                $aliases .= \sprintf(
                                    '<span class="badge" style="margin-left: 5px">%s</span>',
                                    StringUtil::encodeHTML($alias)
                                );
                            }

                            return $aliases;
                        }
                    }
                ),
            GridViewColumn::for("categoryID")
                ->label("wcf.global.category")
                ->filter(
                    new class($this->getSmileyCategories()) extends SelectFilter {
                        #[\Override]
                        public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
                        {
                            if (\intval($value) === 0) {
                                $list->getConditionBuilder()->add("categoryID IS NULL");
                            } else {
                                parent::applyFilter($list, $id, $value);
                            }
                        }
                    }
                )
                ->renderer(
                    new class() extends CategoryColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            if (!$value) {
                                return WCF::getLanguage()->get("wcf.acp.smiley.categoryID.default");
                            }

                            return parent::render($value, $row);
                        }
                    }
                ),
            GridViewColumn::for('showOrder')
                ->label('wcf.global.showOrder')
                ->renderer(new NumberColumnRenderer())
                ->filter(new NumericFilter())
                ->sortable(),
        ]);

        $provider = new SmileyInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(SmileyEditForm::class),
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new SmileyBulkInteractions());

        $this->addRowLink(new GridViewRowLink(SmileyEditForm::class));

        $this->setDefaultSortField("showOrder");
    }

    /**
     * @return array<int, string>
     */
    private function getSmileyCategories(): array
    {
        $smileyCategories = SmileyCache::getInstance()->getCategories();
        $categories = [];
        foreach ($smileyCategories as $category) {
            $categories[$category->categoryID] = $category->getTitle();
        }

        return $categories;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_SMILEY
            && WCF::getSession()->getPermission('admin.content.smiley.canManageSmiley');
    }

    #[\Override]
    protected function createObjectList(): I18nSmileyList
    {
        return new I18nSmileyList();
    }
}
