<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\MenuEditForm;
use wcf\data\box\Box;
use wcf\data\DatabaseObject;
use wcf\data\menu\I18nMenuList;
use wcf\data\menu\Menu;
use wcf\event\gridView\admin\MenuGridViewInitialized;
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
use wcf\system\gridView\renderer\PhraseColumnRenderer;
use wcf\system\interaction\admin\MenuInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\WCF;

/**
 * Grid view for the list of menus.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<Menu, I18nMenuList>
 */
final class MenuGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for("menuID")
                ->label("wcf.global.objectID")
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(new ObjectIdFilter())
                ->sortable(),
            GridViewColumn::for("title")
                ->label("wcf.global.name")
                ->titleColumn()
                ->renderer(new PhraseColumnRenderer())
                ->filter(new I18nTextFilter())
                ->sortable(sortByDatabaseColumn: "titleI18n"),
            GridViewColumn::for("items")
                ->label("wcf.acp.menu.item.list")
                ->filter(new NumericFilter($this->subSelectItems()))
                ->renderer(new NumberColumnRenderer())
                ->sortable(sortByDatabaseColumn: $this->subSelectItems()),
            GridViewColumn::for("position")
                ->label("wcf.acp.box.position")
                ->filter(
                    new SelectFilter(
                        \array_combine(
                            Box::$availableMenuPositions,
                            \array_map(static function (string $postion): string {
                                return 'wcf.acp.box.position.' . $postion;
                            }, Box::$availableMenuPositions)
                        )
                    )
                )
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            return WCF::getLanguage()->get("wcf.acp.box.position." . $value);
                        }
                    }
                )
                ->sortable(sortByDatabaseColumn: $this->subSelectItems()),
            GridViewColumn::for("showOrder")
                ->label("wcf.global.showOrder")
                ->filter(new NumericFilter($this->subSelectItems()))
                ->renderer(new NumberColumnRenderer())
                ->sortable(sortByDatabaseColumn: $this->subSelectItems()),
        ]);

        $provider = new MenuInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(MenuEditForm::class),
        ]);
        $this->setInteractionProvider($provider);

        $this->setDefaultSortField("title");
        $this->addRowLink(new GridViewRowLink(MenuEditForm::class));
    }

    private function subSelectItems(): string
    {
        return "(
            SELECT  COUNT(*)
            FROM    wcf1_menu_item
            WHERE   menuID = menu.menuID
        )";
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission("admin.content.cms.canManageMenu");
    }

    #[\Override]
    protected function createObjectList(): I18nMenuList
    {
        $list = new I18nMenuList();
        if (!empty($list->sqlSelects)) {
            $list->sqlSelects .= ', ';
        }

        $list->sqlSelects .= \sprintf(
            "%s as items, %s as position, %s as showOrder",
            $this->subSelectItems(),
            $this->subSelectPosition(),
            $this->subSelectShowOrder()
        );

        return $list;
    }

    private function subSelectPosition(): string
    {
        return " (
            SELECT  position
            FROM    wcf1_box
            WHERE   menuID = menu.menuID
        )";
    }

    private function subSelectShowOrder(): string
    {
        return "(
            SELECT  showOrder
            FROM    wcf1_box
            WHERE   menuID = menu.menuID
        )";
    }

    #[\Override]
    protected function getInitializedEvent(): MenuGridViewInitialized
    {
        return new MenuGridViewInitialized($this);
    }
}
