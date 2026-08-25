<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserOptionCategoryEditForm;
use wcf\data\DatabaseObject;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryList;
use wcf\event\gridView\admin\UserOptionCategoryGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\UserOptionCategoryInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of user option categories.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractGridView<UserOptionCategory, UserOptionCategoryList>
 */
final class UserOptionCategoryGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('categoryID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('categoryName')
                ->label('wcf.global.name')
                ->titleColumn()
                ->sortable()
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof UserOptionCategory);

                            return StringUtil::encodeHTML($row->getTitle());
                        }
                    }
                ]),
            GridViewColumn::for('userOptions')
                ->label('wcf.acp.user.option.category.options')
                ->renderer(new NumberColumnRenderer())
                ->sortable(sortByDatabaseColumn: $this->subSelectUserOptions(), defaultSortOrder: 'DESC'),
            GridViewColumn::for('showOrder')
                ->label('wcf.global.showOrder')
                ->renderer(new NumberColumnRenderer())
                ->sortable(),
        ]);

        $provider = new UserOptionCategoryInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(UserOptionCategoryEditForm::class),
        ]);
        $this->setInteractionProvider($provider);
        $this->addRowLink(new GridViewRowLink(UserOptionCategoryEditForm::class));
        $this->setDefaultSortField('showOrder');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->hasPermission('admin.user.canManageUserOption');
    }

    #[\Override]
    protected function createObjectList(): UserOptionCategoryList
    {
        $list = new UserOptionCategoryList();
        if ($list->sqlSelects !== '') {
            $list->sqlSelects .= ', ';
        }
        $list->sqlSelects .= $this->subSelectUserOptions() . ' AS userOptions';
        $list->getConditionBuilder()->add('user_option_category.parentCategoryName = ?', ['profile']);

        return $list;
    }

    #[\Override]
    protected function getInitializedEvent(): UserOptionCategoryGridViewInitialized
    {
        return new UserOptionCategoryGridViewInitialized($this);
    }

    private function subSelectUserOptions(): string
    {
        return "(
            SELECT  COUNT(*)
            FROM    wcf1_user_option
            WHERE   categoryName = user_option_category.categoryName
        )";
    }
}
