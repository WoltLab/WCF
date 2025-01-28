<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserOptionEditForm;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionList;
use wcf\event\gridView\admin\UserOptionGridViewInitialized;
use wcf\event\IPsr14Event;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\UserOptionInteractions;
use wcf\system\interaction\bulk\admin\UserOptionBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of user options.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class UserOptionGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('optionID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('optionName')
                ->label('wcf.global.name')
                ->sortable()
                ->titleColumn()
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof UserOption);

                            return StringUtil::encodeHTML($row->getTitle());
                        }
                    }
                ]),
            GridViewColumn::for('categoryName')
                ->label('wcf.global.category')
                ->sortable()
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof UserOption);

                            return StringUtil::encodeHTML(
                                WCF::getLanguage()->get('wcf.user.option.category.' . $row->categoryName)
                            );
                        }
                    }
                ]),
            GridViewColumn::for('optionType')
                ->label('wcf.acp.user.option.optionType')
                ->sortable(),
            GridViewColumn::for('showOrder')
                ->label('wcf.global.showOrder')
                ->sortable()
                ->renderer(new NumberColumnRenderer()),
        ]);

        $provider = new UserOptionInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(UserOptionEditForm::class)
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new UserOptionBulkInteractions());
        $this->addQuickInteraction(
            new ToggleInteraction('enable', 'core/users/options/%s/enable', 'core/users/options/%s/disable')
        );
        $this->addRowLink(new GridViewRowLink(UserOptionEditForm::class));
        $this->setSortField('showOrder');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.user.canManageUserOption');
    }

    #[\Override]
    protected function createObjectList(): DatabaseObjectList
    {
        $list = new UserOptionList();
        $list->getConditionBuilder()->add(
            "option_table.categoryName IN (
                SELECT  categoryName
                FROM    wcf" . WCF_N . "_user_option_category
                WHERE   parentCategoryName = ?
            )",
            ['profile']
        );

        return $list;
    }

    #[\Override]
    protected function getInitializedEvent(): ?IPsr14Event
    {
        return new UserOptionGridViewInitialized($this);
    }
}
